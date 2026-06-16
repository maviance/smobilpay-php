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
        // A 401 triggers one forced refresh + retry; a persistent 401 (e.g. a
        // restricted endpoint) surfaces as SmobilpayApiException after exactly
        // one retry (MPAY-30042). Sequence after the preloaded mint:
        // validate(401) -> re-mint -> validate(401).
        $this->http->addResponse($this->jsonFixture(401, 'customer-account-401.json'));
        $this->http->addResponse($this->jsonFixture(200, 'oauth-token.json'));
        $this->http->addResponse($this->jsonFixture(401, 'customer-account-401.json'));
        try {
            $this->client->accountValidation()->validateAccount('237699999999', 999999);
            self::fail('Expected SmobilpayApiException');
        } catch (SmobilpayApiException $e) {
            self::assertSame(401, $e->httpStatus());
            self::assertNotNull($e->error());
            self::assertSame(40101, $e->error()->respCode);
        }
        // Bounded: mint, validate, re-mint, validate = 4 requests (one retry).
        self::assertCount(4, $this->http->getRequests());
    }

    public function testValidateAccountRetriesOn401ThenSucceeds(): void
    {
        // Sequence after the preloaded mint: validate(401) -> forced re-mint ->
        // validate(200). The retry must carry a refreshed bearer (MPAY-30042).
        $this->http->addResponse($this->jsonFixture(401, 'customer-account-401.json'));
        $this->http->addResponse($this->jsonFixture(200, 'oauth-token.json'));
        $this->http->addResponse($this->jsonFixture(200, 'customer-account.json'));

        $acct = $this->client->accountValidation()->validateAccount('237699999999', 999999);

        self::assertInstanceOf(CustomerAccount::class, $acct);
        self::assertSame(Status::VERIFIED, $acct->status);

        $requests = $this->http->getRequests();
        // mint, validate(401), re-mint, validate(200)
        self::assertCount(4, $requests);
        self::assertSame('/oauth/token', $requests[2]->getUri()->getPath());
        self::assertStringStartsWith('Bearer ', $requests[3]->getHeaderLine('Authorization'));
    }
}
