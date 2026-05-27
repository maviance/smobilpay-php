<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Model;

use Maviance\Smobilpay\Model\CustomerAccount\Status;
use PHPUnit\Framework\TestCase;

final class CustomerAccountStatusTest extends TestCase
{
    public function testWireValuesMatch(): void
    {
        self::assertSame('UNKNOWN', Status::UNKNOWN->value);
        self::assertSame('VALIDATED', Status::VALIDATED->value);
        self::assertSame('VERIFIED', Status::VERIFIED->value);
    }

    public function testTryFromAcceptsAllWireValues(): void
    {
        self::assertSame(Status::UNKNOWN, Status::tryFrom('UNKNOWN'));
        self::assertSame(Status::VALIDATED, Status::tryFrom('VALIDATED'));
        self::assertSame(Status::VERIFIED, Status::tryFrom('VERIFIED'));
    }

    public function testTryFromUnknownReturnsNull(): void
    {
        self::assertNull(Status::tryFrom('FOO'));
    }
}
