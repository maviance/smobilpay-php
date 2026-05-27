<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Http;

use BackedEnum;
use DateTimeImmutable;
use DateTimeInterface;
use Maviance\Smobilpay\Exception\SmobilpayParseException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use stdClass;
use Throwable;

/**
 * Encodes outbound DTOs to JSON and decodes inbound JSON into readonly DTOs.
 *
 * Encoding rules:
 *  - Reflects public properties of the object (readonly DTOs use constructor
 *    property promotion, so all promoted props are public).
 *  - Skips `null` properties (mirrors Jackson's `@JsonInclude(NON_NULL)`).
 *  - {@see DateTimeInterface} → ISO 8601 offset datetime.
 *  - {@see BackedEnum} → its `value`.
 *  - {@see UuidInterface} → its canonical string.
 *  - Nested objects recurse; lists/arrays recurse element-wise.
 *
 * Decoding rules:
 *  - Reads the target class's constructor; maps JSON keys to parameter names.
 *  - Builds constructor args as an associative array of `name => value` and
 *    invokes via `ReflectionClass::newInstanceArgs()` which, since PHP 8.0,
 *    supports named arguments — so missing optional fields simply don't
 *    appear in the map (no positional drift).
 *  - Coerces primitives, recurses into typed object parameters.
 *  - {@see BackedEnum} → `Enum::from($value)` (must be a valid case).
 *  - {@see DateTimeImmutable} → {@see LenientDateParser::parse()}.
 *  - {@see UuidInterface} → {@see Uuid::fromString()}.
 *  - `array` parameters with a {@see ListOf} attribute hydrate each element
 *    as the declared class. Bare `array` stays a plain array.
 *  - Unknown JSON keys are ignored (forward-compatibility — matches Java's
 *    `FAIL_ON_UNKNOWN_PROPERTIES = false`).
 *  - Missing required parameters throw {@see SmobilpayParseException}.
 *  - {@see stdClass} target: returns a stdClass with the decoded object's
 *    keys copied verbatim.
 */
final class JsonSerializer
{
    /**
     * Decode a JSON body into a target type.
     *
     * @template T of object
     * @param class-string<T>|array{0: class-string<T>} $type
     *        Class name to hydrate a single object, or `[ClassName::class]`
     *        to hydrate a list of that class.
     * @return T|list<T>
     */
    public function decode(string $body, string|array $type): mixed
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            throw new SmobilpayParseException('Cannot decode empty response body');
        }
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($trimmed, true, 512, \JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new SmobilpayParseException(
                'Response body is not valid JSON: ' . $e->getMessage(),
                $e,
            );
        }

        if (\is_array($type)) {
            $elementClass = $type[0] ?? null;
            if (!\is_string($elementClass)) {
                throw new SmobilpayParseException('List type must be ["ClassName::class"]');
            }
            if (!\is_array($decoded)) {
                throw new SmobilpayParseException(\sprintf(
                    'Expected JSON array for list of %s, got %s',
                    $elementClass,
                    get_debug_type($decoded),
                ));
            }
            /** @var list<T> $out */
            $out = [];
            foreach ($decoded as $i => $element) {
                if (!\is_array($element)) {
                    throw new SmobilpayParseException(\sprintf(
                        'Expected object at index %d of %s list, got %s',
                        (int) $i,
                        $elementClass,
                        get_debug_type($element),
                    ));
                }
                /** @var array<string, mixed> $element */
                $out[] = $this->hydrate($elementClass, $element);
            }

            return $out;
        }

        if (!\is_array($decoded)) {
            throw new SmobilpayParseException(\sprintf(
                'Expected JSON object for %s, got %s',
                $type,
                get_debug_type($decoded),
            ));
        }
        /** @var array<string, mixed> $decoded */
        return $this->hydrate($type, $decoded);
    }

    /**
     * Decode a body that is a bare JSON primitive (`true`, `false`, `null`,
     * a number, or a string). Used by endpoints whose response schema is a
     * primitive — most notably `GET /v2/verify` which returns a bare boolean.
     */
    public function decodePrimitive(string $body): mixed
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            throw new SmobilpayParseException('Cannot decode empty response body');
        }
        try {
            return json_decode($trimmed, true, 16, \JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new SmobilpayParseException(
                'Response body is not valid JSON: ' . $e->getMessage(),
                $e,
            );
        }
    }

    /**
     * Encode a DTO into JSON. Properties whose value is `null` are skipped.
     */
    public function encode(object $value): string
    {
        $payload = $this->normalize($value);
        try {
            return json_encode($payload, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new SmobilpayParseException(
                'Failed to encode request body: ' . $e->getMessage(),
                $e,
            );
        }
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<string, mixed> $data
     * @return T
     */
    private function hydrate(string $class, array $data): object
    {
        if ($class === stdClass::class) {
            $obj = new stdClass();
            foreach ($data as $k => $v) {
                $obj->{(string) $k} = $v;
            }
            /** @var T &stdClass $obj */
            return $obj;
        }

        $ref = new ReflectionClass($class);
        $ctor = $ref->getConstructor();
        if ($ctor === null) {
            /** @var T $instance */
            $instance = $ref->newInstance();

            return $instance;
        }

        // Build an associative map of named arguments so optional missing
        // fields simply don't appear (PHP 8 newInstanceArgs supports named
        // args). This avoids positional drift when JSON omits optional
        // fields before required-by-position-but-actually-optional ones.
        $args = [];
        foreach ($ctor->getParameters() as $param) {
            $name = $param->getName();
            $present = \array_key_exists($name, $data);

            if (!$present) {
                if ($param->isOptional()) {
                    continue;
                }
                if ($param->allowsNull()) {
                    $args[$name] = null;
                    continue;
                }
                throw new SmobilpayParseException(\sprintf(
                    'Missing required field "%s" for %s',
                    $name,
                    $class,
                ));
            }

            /** @var mixed $raw */
            $raw = $data[$name];
            $args[$name] = $this->coerce($raw, $param, $class);
        }

        /** @var T $instance */
        $instance = $ref->newInstanceArgs($args);

        return $instance;
    }

    private function coerce(mixed $raw, ReflectionParameter $param, string $owningClass): mixed
    {
        if ($raw === null) {
            if ($param->allowsNull()) {
                return null;
            }
            throw new SmobilpayParseException(\sprintf(
                'Field "%s" on %s is non-nullable but JSON value was null',
                $param->getName(),
                $owningClass,
            ));
        }

        $type = $param->getType();
        if ($type === null) {
            return $raw;
        }

        if ($type instanceof ReflectionUnionType) {
            // We only support union types of shape `Foo|null`. Reflection
            // represents `?Foo` as a NamedType (with allowsNull=true) and
            // `Foo|null` as a UnionType. Pick the first non-null branch.
            foreach ($type->getTypes() as $sub) {
                if ($sub instanceof ReflectionNamedType && $sub->getName() !== 'null') {
                    return $this->coerceNamed($raw, $sub, $param, $owningClass);
                }
            }
            throw new SmobilpayParseException(\sprintf(
                'Unsupported union type on field "%s" of %s',
                $param->getName(),
                $owningClass,
            ));
        }

        if (!$type instanceof ReflectionNamedType) {
            return $raw;
        }

        return $this->coerceNamed($raw, $type, $param, $owningClass);
    }

    private function coerceNamed(
        mixed $raw,
        ReflectionNamedType $type,
        ReflectionParameter $param,
        string $owningClass,
    ): mixed {
        $name = $type->getName();

        if ($type->isBuiltin()) {
            return match ($name) {
                'int' => \is_int($raw) ? $raw : (int) $raw,
                'float' => \is_float($raw) ? $raw : (float) (\is_int($raw) ? $raw : (string) $raw),
                'bool' => (bool) $raw,
                'string' => \is_string($raw) ? $raw : (string) $raw,
                'array' => $this->coerceArray($raw, $param, $owningClass),
                'mixed' => $raw,
                default => $raw,
            };
        }

        if (is_a($name, BackedEnum::class, true)) {
            /** @var class-string<BackedEnum> $name */
            if (!\is_string($raw) && !\is_int($raw)) {
                throw new SmobilpayParseException(\sprintf(
                    'Expected scalar for enum field "%s" of %s, got %s',
                    $param->getName(),
                    $owningClass,
                    get_debug_type($raw),
                ));
            }
            $candidate = $name::tryFrom($raw);
            if ($candidate === null) {
                throw new SmobilpayParseException(\sprintf(
                    'Unknown enum value "%s" for %s on field "%s" of %s',
                    (string) $raw,
                    $name,
                    $param->getName(),
                    $owningClass,
                ));
            }

            return $candidate;
        }

        if (is_a($name, DateTimeInterface::class, true) || $name === DateTimeImmutable::class) {
            if (!\is_string($raw)) {
                throw new SmobilpayParseException(\sprintf(
                    'Expected string for date field "%s" of %s, got %s',
                    $param->getName(),
                    $owningClass,
                    get_debug_type($raw),
                ));
            }
            $parsed = LenientDateParser::parse($raw);
            if ($parsed === null) {
                throw new SmobilpayParseException(\sprintf(
                    'Blank date for non-nullable field "%s" of %s',
                    $param->getName(),
                    $owningClass,
                ));
            }

            return $parsed;
        }

        if (is_a($name, UuidInterface::class, true)) {
            if (!\is_string($raw)) {
                throw new SmobilpayParseException(\sprintf(
                    'Expected string for UUID field "%s" of %s, got %s',
                    $param->getName(),
                    $owningClass,
                    get_debug_type($raw),
                ));
            }

            return Uuid::fromString($raw);
        }

        // Nested object — recurse
        if (\is_array($raw) && class_exists($name)) {
            /** @var array<string, mixed> $raw */
            /** @var class-string $name */
            return $this->hydrate($name, $raw);
        }

        return $raw;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function coerceArray(mixed $raw, ReflectionParameter $param, string $owningClass): array
    {
        if (!\is_array($raw)) {
            throw new SmobilpayParseException(\sprintf(
                'Expected array for field "%s" of %s, got %s',
                $param->getName(),
                $owningClass,
                get_debug_type($raw),
            ));
        }

        $listOfAttrs = $param->getAttributes(ListOf::class);
        if ($listOfAttrs === []) {
            return $raw;
        }
        /** @var ListOf<object> $listOf */
        $listOf = $listOfAttrs[0]->newInstance();
        $elementClass = $listOf->elementType;

        $out = [];
        foreach ($raw as $i => $element) {
            if (!\is_array($element)) {
                throw new SmobilpayParseException(\sprintf(
                    'Expected object at index %d of #[ListOf(%s)] field "%s" on %s, got %s',
                    (int) $i,
                    $elementClass,
                    $param->getName(),
                    $owningClass,
                    get_debug_type($element),
                ));
            }
            /** @var array<string, mixed> $element */
            $out[] = $this->hydrate($elementClass, $element);
        }

        return $out;
    }

    /**
     * Convert a value into JSON-encodable primitives. Recurses into objects.
     */
    private function normalize(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        if (\is_scalar($value)) {
            return $value;
        }
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }
        if ($value instanceof UuidInterface) {
            return $value->toString();
        }
        if (\is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $normalized = $this->normalize($v);
                if ($normalized === null) {
                    continue;
                }
                $out[$k] = $normalized;
            }

            return $out;
        }
        if (\is_object($value)) {
            $out = [];
            $ref = new ReflectionClass($value);
            foreach ($ref->getProperties() as $prop) {
                if (!$prop->isPublic()) {
                    continue;
                }
                /** @var mixed $raw */
                $raw = $prop->getValue($value);
                if ($raw === null) {
                    continue;
                }
                $out[$prop->getName()] = $this->normalize($raw);
            }

            return $out;
        }

        return $value;
    }
}
