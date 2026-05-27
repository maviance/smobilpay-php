<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Integration;

use Maviance\Smobilpay\Exception\SmobilpayApiException;
use Maviance\Smobilpay\Model\CollectionRequest;
use Maviance\Smobilpay\Model\CollectionResponse;
use Maviance\Smobilpay\Model\PaymentStatusType;
use Ramsey\Uuid\Uuid;

final class ConfirmApiTest extends ApiTestCase
{
    public function testCollectPostsJsonAndHydratesResponse(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'collect-response.json'));
        $request = new CollectionRequest(
            quoteId: Uuid::fromString('0e1f7f4a-3b2c-4a8d-9d1f-1f5d2c3a4b6e'),
            customerPhonenumber: '237699999999',
            customerEmailaddress: 'customer@example.com',
            customerName: 'Jane Doe',
            trid: 'ORDER-2026-05-02-0001',
            tag: 'retail-front-desk',
        );
        $response = $this->client->confirm()->collect($request);

        $req = $this->lastApiRequest();
        $this->assertApiRequest($req, 'POST', '/v2/collectstd', '');
        self::assertSame('application/json', $req->getHeaderLine('Content-Type'));
        $sent = json_decode((string) $req->getBody(), true);
        self::assertIsArray($sent);
        self::assertSame('0e1f7f4a-3b2c-4a8d-9d1f-1f5d2c3a4b6e', $sent['quoteId']);
        self::assertSame('Jane Doe', $sent['customerName']);
        self::assertArrayNotHasKey('callbackUrl', $sent, 'null fields must be omitted');

        self::assertInstanceOf(CollectionResponse::class, $response);
        self::assertSame('PTN-202605020800001', $response->ptn);
        self::assertSame(PaymentStatusType::PENDING, $response->status);
    }

    public function testQuoteExpired498SurfacesAsApiException(): void
    {
        $this->http->addResponse($this->jsonFixture(498, 'error-quote-expired-498.json'));
        $request = new CollectionRequest(
            quoteId: Uuid::fromString('0e1f7f4a-3b2c-4a8d-9d1f-1f5d2c3a4b6e'),
            customerPhonenumber: '237699999999',
            customerEmailaddress: 'customer@example.com',
        );
        try {
            $this->client->confirm()->collect($request);
            self::fail('Expected SmobilpayApiException');
        } catch (SmobilpayApiException $e) {
            self::assertSame(498, $e->httpStatus());
            self::assertNotNull($e->error());
            self::assertSame(49801, $e->error()->respCode);
        }
    }
}
