<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Model;

use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\Model\QuoteRequest;
use PHPUnit\Framework\TestCase;

final class QuoteRequestTest extends TestCase
{
    public function testValid(): void
    {
        $r = new QuoteRequest(1, 'PI-X');
        self::assertSame(1, $r->amount);
        self::assertSame('PI-X', $r->payItemId);
    }

    public function testZeroAmountRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new QuoteRequest(0, 'PI-X');
    }

    public function testNegativeAmountRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new QuoteRequest(-1, 'PI-X');
    }

    public function testEmptyPayItemIdRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new QuoteRequest(100, '');
    }
}
