<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Http;

use Maviance\Smobilpay\Exception\SmobilpayParseException;
use Maviance\Smobilpay\Http\JsonSerializer;
use Maviance\Smobilpay\Model\ApiError;
use Maviance\Smobilpay\Model\Merchant;
use PHPUnit\Framework\TestCase;
use stdClass;

final class JsonSerializerEdgeCasesTest extends TestCase
{
    private function s(): JsonSerializer
    {
        return new JsonSerializer();
    }

    public function testDecodePrimitiveTrue(): void
    {
        self::assertTrue($this->s()->decodePrimitive('true'));
    }

    public function testDecodePrimitiveFalse(): void
    {
        self::assertFalse($this->s()->decodePrimitive('false'));
    }

    public function testDecodePrimitiveNumber(): void
    {
        self::assertSame(42, $this->s()->decodePrimitive('42'));
    }

    public function testDecodePrimitiveEmptyThrows(): void
    {
        $this->expectException(SmobilpayParseException::class);
        $this->s()->decodePrimitive('');
    }

    public function testDecodePrimitiveMalformedThrows(): void
    {
        $this->expectException(SmobilpayParseException::class);
        $this->s()->decodePrimitive('{broken');
    }

    public function testDecodeListWithNonArrayBodyThrows(): void
    {
        $this->expectException(SmobilpayParseException::class);
        $this->s()->decode('{"not": "an array"}', [Merchant::class]);
    }

    public function testDecodeListWithNonObjectElementThrows(): void
    {
        $this->expectException(SmobilpayParseException::class);
        $this->s()->decode('["string-not-object"]', [Merchant::class]);
    }

    public function testDecodeObjectAsArrayShorthandRejects(): void
    {
        // The shorthand [class] only works for JSON arrays.
        $this->expectException(SmobilpayParseException::class);
        $this->s()->decode('"plain-string"', ApiError::class);
    }

    public function testHydrateStdClass(): void
    {
        /** @var stdClass $obj */
        $obj = $this->s()->decode('{"foo":1,"bar":"x"}', stdClass::class);
        self::assertSame(1, $obj->foo);
        self::assertSame('x', $obj->bar);
    }

    public function testEncodeArrayValueWithNestedNulls(): void
    {
        // Use a fresh anonymous object whose property is an array containing
        // null values — encoder should strip them.
        $obj = new class () {
            /** @var array<string, mixed> */
            public readonly array $items;

            public function __construct()
            {
                $this->items = ['a' => 1, 'b' => null, 'c' => 3];
            }
        };
        $json = $this->s()->encode($obj);
        self::assertSame('{"items":{"a":1,"c":3}}', $json);
    }
}
