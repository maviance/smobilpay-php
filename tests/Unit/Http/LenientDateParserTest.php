<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Http;

use Maviance\Smobilpay\Exception\SmobilpayParseException;
use Maviance\Smobilpay\Http\LenientDateParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LenientDateParserTest extends TestCase
{
    public function testNullReturnsNull(): void
    {
        self::assertNull(LenientDateParser::parse(null));
    }

    public function testEmptyStringReturnsNull(): void
    {
        self::assertNull(LenientDateParser::parse(''));
        self::assertNull(LenientDateParser::parse('   '));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function acceptedFormats(): array
    {
        return [
            'iso date only'           => ['2025-11-05',                       '2025-11-05'],
            'iso offset datetime'     => ['2025-11-05T00:00:00+01:00',        '2025-11-05'],
            'iso offset datetime utc' => ['2025-11-05T12:34:56+00:00',        '2025-11-05'],
            'iso zoned z'             => ['2025-11-05T12:34:56Z',             '2025-11-05'],
            'iso local datetime'      => ['2025-11-05T12:34:56',              '2025-11-05'],
            'fractional seconds z'    => ['2025-11-05T12:34:56.789012Z',      '2025-11-05'],
            'fractional seconds offset' => ['2025-11-05T12:34:56.500000+01:00', '2025-11-05'],
            'trailing whitespace'     => ['  2025-11-05  ',                   '2025-11-05'],
        ];
    }

    #[DataProvider('acceptedFormats')]
    public function testAcceptsKnownFormats(string $input, string $expectedDate): void
    {
        $result = LenientDateParser::parse($input);
        self::assertNotNull($result);
        self::assertSame($expectedDate, $result->format('Y-m-d'));
    }

    public function testJunkThrows(): void
    {
        $this->expectException(SmobilpayParseException::class);
        LenientDateParser::parse('not-a-date');
    }
}
