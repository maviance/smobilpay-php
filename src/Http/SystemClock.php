<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Http;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;

/**
 * Default {@see ClockInterface} implementation. Returns the current UTC time.
 * Internal — partners can pass any PSR-20 implementation to
 * {@see \Maviance\Smobilpay\SmobilpayConfig} for testability.
 */
final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
