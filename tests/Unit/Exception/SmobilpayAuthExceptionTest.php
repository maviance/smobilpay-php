<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Exception;

use Maviance\Smobilpay\Exception\SmobilpayAuthException;
use PHPUnit\Framework\TestCase;

final class SmobilpayAuthExceptionTest extends TestCase
{
    public function testCarriesFields(): void
    {
        $ex = new SmobilpayAuthException(401, 'invalid_client', 'rejected');
        self::assertSame(401, $ex->httpStatus());
        self::assertSame('invalid_client', $ex->oauthError());
        self::assertSame('rejected', $ex->getMessage());
    }

    public function testNullOauthError(): void
    {
        $ex = new SmobilpayAuthException(502, null, 'gateway error');
        self::assertNull($ex->oauthError());
        self::assertSame(502, $ex->httpStatus());
    }
}
