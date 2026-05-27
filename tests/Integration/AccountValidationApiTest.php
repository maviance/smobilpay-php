<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Integration;

use Maviance\Smobilpay\Exception\SmobilpayApiException;
use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\Model\CustomerAccount;
use Maviance\Smobilpay\Model\CustomerAccount\Status;

final class AccountValidationApiTest extends ApiTestCase
{
    public function testVerifyServiceNumberTrue(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'verify-true.json'));
        $ok = $this->client->accountValidation()->verifyServiceNumber('ENEO', 1234, '01234567');
        self::assertTrue($ok);
        $req = $this->lastApiRequest();
        $this->assertApiRequest(
            $req,
            'GET',
            '/v2/verify',
            'merchant=ENEO&serviceid=1234&serviceNumber=01234567',
        );
    }

    public function testVerifyServiceNumberFalse(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'verify-false.json'));
        $ok = $this->client->accountValidation()->verifyServiceNumber('ENEO', 1234, '01234567');
        self::assertFalse($ok);
    }

    public function testVerifyEmptyMerchantRejectedClientSide(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        $this->client->accountValidation()->verifyServiceNumber('', 1234, '01234567');
    }

    public function testValidateAccountReturnsCustomerAccount(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'customer-account.json'));
        $acct = $this->client->accountValidation()->validateAccount('237699999999', 999999);
        $this->assertApiRequest(
            $this->lastApiRequest(),
            'GET',
            '/v2/validate',
            'destination=237699999999&serviceId=999999',
        );
        self::assertInstanceOf(CustomerAccount::class, $acct);
        self::assertSame(Status::VERIFIED, $acct->status);
        self::assertSame('Jane Subscriber', $acct->name);
        self::assertSame('237699999999', $acct->destination);
    }

    public function testValidateAccount401RestrictedSurfacesAsApiException(): void
    {
        $this->http->addResponse($this->jsonFixture(401, 'customer-account-401.json'));
        try {
            $this->client->accountValidation()->validateAccount('237699999999', 999999);
            self::fail('Expected SmobilpayApiException');
        } catch (SmobilpayApiException $e) {
            self::assertSame(401, $e->httpStatus());
            self::assertNotNull($e->error());
            self::assertSame(40101, $e->error()->respCode);
        }
    }
}
