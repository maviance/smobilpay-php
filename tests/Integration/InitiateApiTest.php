<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Integration;

use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\Model\Bill;
use Maviance\Smobilpay\Model\BillType;
use Maviance\Smobilpay\Model\QuoteRequest;
use Maviance\Smobilpay\Model\QuoteResponse;
use Maviance\Smobilpay\Model\Subscription;

final class InitiateApiTest extends ApiTestCase
{
    public function testBills(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'bill.json'));
        $bills = $this->client->initiate()->bills('CDE', 4321, 'METER-001');
        $this->assertApiRequest(
            $this->lastApiRequest(),
            'GET',
            '/v2/bill',
            'merchant=CDE&serviceid=4321&serviceNumber=METER-001',
        );
        self::assertContainsOnlyInstancesOf(Bill::class, $bills);
        self::assertSame(BillType::REGULAR, $bills[0]->billType);
        self::assertSame(13450.0, $bills[0]->amountLocalCur);
        // Verifies the LenientDateParser handled both YYYY-MM-DD and the offset datetime variant.
        self::assertNotNull($bills[0]->billDate);
        self::assertSame('2026-04-01', $bills[0]->billDate->format('Y-m-d'));
        self::assertNotNull($bills[0]->billDueDate);
        self::assertSame('2026-04-30', $bills[0]->billDueDate->format('Y-m-d'));
    }

    public function testSubscriptionsByServiceNumber(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'subscription.json'));
        $subs = $this->client->initiate()->subscriptions(
            'CANALPLUS',
            100500,
            serviceNumber: 'DECODER-001234',
        );
        $this->assertApiRequest(
            $this->lastApiRequest(),
            'GET',
            '/v2/subscription',
            'merchant=CANALPLUS&serviceid=100500&serviceNumber=DECODER-001234',
        );
        self::assertContainsOnlyInstancesOf(Subscription::class, $subs);
        self::assertSame('Jane Subscriber', $subs[0]->customerName);
    }

    public function testSubscriptionsRequiresOneOfServiceOrCustomerNumber(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        $this->client->initiate()->subscriptions('CANALPLUS', 100500);
    }

    public function testQuotePostsJsonBody(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'quote-response.json'));
        $quote = $this->client->initiate()->quote(new QuoteRequest(5000, 'PI-ENEO-PREPAID-001'));
        $req = $this->lastApiRequest();
        $this->assertApiRequest($req, 'POST', '/v2/quotestd', '');
        self::assertSame('application/json', $req->getHeaderLine('Content-Type'));
        self::assertSame(
            '{"amount":5000,"payItemId":"PI-ENEO-PREPAID-001"}',
            (string) $req->getBody(),
        );
        self::assertInstanceOf(QuoteResponse::class, $quote);
        self::assertSame('PI-ENEO-PREPAID-001', $quote->payItemId);
        self::assertSame('0e1f7f4a-3b2c-4a8d-9d1f-1f5d2c3a4b6e', $quote->quoteId->toString());
    }

    public function testQuoteResendsBodyOnRetryAfter401(): void
    {
        // POST /v2/quotestd: 401 once, forced re-mint, then 200. The retry must
        // resend the JSON body (the first attempt consumed the body stream) and
        // carry a refreshed bearer (MPAY-30042).
        $this->http->addResponse($this->jsonFixture(401, 'customer-account-401.json'));
        $this->http->addResponse($this->jsonFixture(200, 'oauth-token.json'));
        $this->http->addResponse($this->jsonFixture(200, 'quote-response.json'));

        $quote = $this->client->initiate()->quote(new QuoteRequest(5000, 'PI-ENEO-PREPAID-001'));
        self::assertInstanceOf(QuoteResponse::class, $quote);

        $requests = $this->http->getRequests();
        // mint, quote(401), re-mint, quote(200)
        self::assertCount(4, $requests);
        $retry = $requests[3];
        self::assertSame('POST', $retry->getMethod());
        self::assertSame('/v2/quotestd', $retry->getUri()->getPath());
        self::assertStringStartsWith('Bearer ', $retry->getHeaderLine('Authorization'));
        self::assertSame(
            '{"amount":5000,"payItemId":"PI-ENEO-PREPAID-001"}',
            (string) $retry->getBody(),
        );
    }
}
