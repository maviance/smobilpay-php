<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Exception;

use Maviance\Smobilpay\Exception\SmobilpayApiException;
use Maviance\Smobilpay\Model\ApiError;
use PHPUnit\Framework\TestCase;

final class SmobilpayApiExceptionTest extends TestCase
{
    public function testFieldsCarriedFromConstructor(): void
    {
        $err = new ApiError(40404, 'gone', 'Not found.', null);
        $ex = new SmobilpayApiException(404, $err, '{"respCode":40404}');
        self::assertSame(404, $ex->httpStatus());
        self::assertSame($err, $ex->error());
        self::assertSame('{"respCode":40404}', $ex->rawBody());
        self::assertStringContainsString('respCode=40404', $ex->getMessage());
    }

    public function testNullErrorEnvelopeProducesUsableMessage(): void
    {
        $ex = new SmobilpayApiException(401, null, '');
        self::assertNull($ex->error());
        self::assertStringContainsString('HTTP 401', $ex->getMessage());
        self::assertStringContainsString('<empty body>', $ex->getMessage());
    }
}
