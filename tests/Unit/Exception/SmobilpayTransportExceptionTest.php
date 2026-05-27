<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Exception;

use Maviance\Smobilpay\Exception\SmobilpayTransportException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

final class SmobilpayTransportExceptionTest extends TestCase
{
    public function testCarriesPsr18Exception(): void
    {
        $psr18 = new class ('boom') extends RuntimeException implements ClientExceptionInterface {};
        $ex = new SmobilpayTransportException('upstream down', $psr18);
        self::assertSame('upstream down', $ex->getMessage());
        self::assertSame($psr18, $ex->psr18Exception());
        self::assertSame($psr18, $ex->getPrevious());
    }

    public function testWithoutPsr18Exception(): void
    {
        $ex = new SmobilpayTransportException('plain');
        self::assertNull($ex->psr18Exception());
        self::assertNull($ex->getPrevious());
    }
}
