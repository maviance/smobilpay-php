<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit;

use DateInterval;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class FakeClock implements ClockInterface
{
    public function __construct(private DateTimeImmutable $now)
    {
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function set(DateTimeImmutable $now): void
    {
        $this->now = $now;
    }

    public function advance(DateInterval|string $interval): void
    {
        if (\is_string($interval)) {
            $interval = new DateInterval($interval);
        }
        $this->now = $this->now->add($interval);
    }
}
