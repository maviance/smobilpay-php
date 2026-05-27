<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Http;

use DateTimeImmutable;
use DateTimeZone;
use Maviance\Smobilpay\Exception\SmobilpayParseException;
use Throwable;

/**
 * Tolerant date parser for the Smobilpay wire format.
 *
 * The partner API documents date-only fields (e.g. `Bill.billDate`,
 * `Subscription.dueDate`) but its acceptance environment is known to
 * occasionally emit them as ISO datetimes with an offset (e.g.
 * `2025-11-05T00:00:00+01:00`). A strict ISO local-date parser rejects
 * those, breaking otherwise-valid responses.
 *
 * This parser accepts, in order:
 *  - `YYYY-MM-DD`                            — ISO local date (treated as UTC midnight)
 *  - `YYYY-MM-DDTHH:MM:SS+HH:MM`             — ISO offset datetime
 *  - `YYYY-MM-DDTHH:MM:SS[Z]`                — ISO zoned datetime / instant
 *  - `YYYY-MM-DDTHH:MM:SS`                   — ISO local datetime (assumed UTC)
 *  - `YYYY-MM-DDTHH:MM:SS.uuuuuu[Z|±HH:MM]`  — fractional-second variants
 *
 * Null and blank input return `null`.
 *
 * Mirrors the Java client's `LenientLocalDateDeserializer` behaviour for
 * cross-language smoke-test parity.
 */
final class LenientDateParser
{
    private const FORMATS = [
        'Y-m-d',
        \DateTimeInterface::ATOM,
        'Y-m-d\TH:i:sP',
        'Y-m-d\TH:i:s\Z',
        'Y-m-d\TH:i:s',
        'Y-m-d\TH:i:s.uP',
        'Y-m-d\TH:i:s.u\Z',
        'Y-m-d\TH:i:s.u',
    ];

    public static function parse(?string $text): ?DateTimeImmutable
    {
        if ($text === null) {
            return null;
        }
        $trimmed = \trim($text);
        if ($trimmed === '') {
            return null;
        }

        $utc = new DateTimeZone('UTC');
        foreach (self::FORMATS as $fmt) {
            $parsed = DateTimeImmutable::createFromFormat($fmt, $trimmed, $utc);
            if ($parsed !== false) {
                return $parsed;
            }
        }
        // Last-resort permissive parse — PHP's constructor handles common
        // ISO variants we may have missed in the explicit list.
        try {
            return new DateTimeImmutable($trimmed, $utc);
        } catch (Throwable $e) {
            throw new SmobilpayParseException(
                "Cannot parse date/time value: '{$trimmed}'",
                $e,
            );
        }
    }
}
