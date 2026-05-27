<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Http;

use BackedEnum;
use DateTimeInterface;
use Stringable;

/**
 * Tiny builder for URL-encoded query strings.
 *
 * Skips `null` values so optional parameters can be added unconditionally.
 * Insertion order is preserved in the encoded output for stable,
 * snapshot-testable URLs.
 *
 * Accepted value types:
 *  - scalar (string, int, float, bool — `true`/`false` encode as `"true"`/`"false"`)
 *  - {@see BackedEnum} (uses `->value`)
 *  - {@see DateTimeInterface} (uses `->format(DATE_ATOM)`)
 *  - {@see Stringable}
 *
 * Anything else throws {@see \TypeError}.
 */
final class QueryParams
{
    /** @var list<array{0: string, 1: string}> */
    private array $entries = [];

    private function __construct()
    {
    }

    public static function of(): self
    {
        return new self();
    }

    /**
     * Add a parameter unless `$value` is `null`.
     */
    public function add(string $name, string|int|float|bool|BackedEnum|DateTimeInterface|Stringable|null $value): self
    {
        if ($value === null) {
            return $this;
        }
        $this->entries[] = [$name, $this->stringify($value)];

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->entries === [];
    }

    /**
     * Returns the encoded query string, without a leading `?`.
     */
    public function encode(): string
    {
        if ($this->entries === []) {
            return '';
        }
        $parts = [];
        foreach ($this->entries as [$name, $value]) {
            $parts[] = rawurlencode($name) . '=' . rawurlencode($value);
        }

        return implode('&', $parts);
    }

    private function stringify(string|int|float|bool|BackedEnum|DateTimeInterface|Stringable $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }
        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (\is_float($value)) {
            // Avoid scientific notation; mirrors PHP default casting but stable for tests.
            return rtrim(rtrim(\sprintf('%.14F', $value), '0'), '.');
        }

        return (string) $value;
    }
}
