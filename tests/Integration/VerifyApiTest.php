<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Integration;

use DateTimeImmutable;
use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\Model\Account;
use Maviance\Smobilpay\Model\PaymentStatus;
use Maviance\Smobilpay\Model\PaymentStatusType;
use Maviance\Smobilpay\Model\Ping;

final class VerifyApiTest extends ApiTestCase
{
    public function testPing(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'ping.json'));
        $pong = $this->client->verify()->ping();
        $this->assertApiRequest($this->lastApiRequest(), 'GET', '/v2/ping', '');
        self::assertInstanceOf(Ping::class, $pong);
        self::assertSame('3.0.0', $pong->version);
    }

    public function testAccount(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'account.json'));
        $acct = $this->client->verify()->account();
        $this->assertApiRequest($this->lastApiRequest(), 'GET', '/v2/account', '');
        self::assertInstanceOf(Account::class, $acct);
        self::assertSame(1245.32, $acct->balance);
        self::assertSame('Jane Operator', $acct->agentName);
    }

    public function testVerifyTransactionByPtn(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'verifytx.json'));
        $rows = $this->client->verify()->verifyTransaction(ptn: 'PTN-202605020800001');
        $this->assertApiRequest(
            $this->lastApiRequest(),
            'GET',
            '/v2/verifytx',
            'ptn=PTN-202605020800001',
        );
        self::assertContainsOnlyInstancesOf(PaymentStatus::class, $rows);
        self::assertSame(PaymentStatusType::SUCCESS, $rows[0]->status);
        self::assertNotNull($rows[0]->commission);
        self::assertSame(25.25, $rows[0]->commission->earnings);
    }

    public function testVerifyTransactionRequiresAtLeastOne(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        $this->client->verify()->verifyTransaction();
    }

    /**
     * Regression: the acceptance environment returns `"errorCode": null` on
     * /v2/verifytx even though the spec types it as a number. Confirm we
     * decode that without a SmobilpayParseException.
     */
    public function testVerifyTransactionAcceptsNullErrorCode(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'verifytx-null-errorcode.json'));
        $rows = $this->client->verify()->verifyTransaction(ptn: 'PTN-202605020800001');
        self::assertCount(1, $rows);
        self::assertNull($rows[0]->errorCode);
    }

    public function testHistoryByPtn(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'history.json'));
        $rows = $this->client->verify()->historyByPtn('PTN-202605020800001');
        $this->assertApiRequest(
            $this->lastApiRequest(),
            'GET',
            '/v2/historystd',
            'ptn=PTN-202605020800001',
        );
        self::assertCount(2, $rows);
    }

    public function testHistoryByDateRangeEncodesAtomBoundsAtUtc(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'history.json'));
        $from = new DateTimeImmutable('2026-04-25T12:34:56+02:00');
        $to = new DateTimeImmutable('2026-05-02T18:00:00+02:00');
        $this->client->verify()->historyByDateRange($from, $to);

        $req = $this->lastApiRequest();
        $query = $req->getUri()->getQuery();
        self::assertStringContainsString('timestamp_from=2026-04-25T00%3A00%3A00%2B00%3A00', $query);
        self::assertStringContainsString('timestamp_to=2026-05-02T23%3A59%3A59%2B00%3A00', $query);
    }

    public function testHistoryByDateRangeRejectsReversedRange(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        $this->client->verify()->historyByDateRange(
            new DateTimeImmutable('2026-05-02'),
            new DateTimeImmutable('2026-04-25'),
        );
    }
}
