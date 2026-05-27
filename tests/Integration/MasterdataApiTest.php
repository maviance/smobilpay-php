<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Integration;

use Maviance\Smobilpay\Model\Cashin;
use Maviance\Smobilpay\Model\Cashout;
use Maviance\Smobilpay\Model\Merchant;
use Maviance\Smobilpay\Model\MerchantStatus;
use Maviance\Smobilpay\Model\Product;
use Maviance\Smobilpay\Model\Service;
use Maviance\Smobilpay\Model\ServiceType;
use Maviance\Smobilpay\Model\Topup;

final class MasterdataApiTest extends ApiTestCase
{
    public function testMerchants(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'merchants.json'));
        $list = $this->client->masterdata()->merchants();
        $this->assertApiRequest($this->lastApiRequest(), 'GET', '/v2/merchant', '');
        self::assertContainsOnlyInstancesOf(Merchant::class, $list);
        self::assertCount(3, $list);
        self::assertSame('ENEO', $list[0]->merchant);
        self::assertSame(MerchantStatus::Active, $list[0]->status);
    }

    public function testServices(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'services.json'));
        $list = $this->client->masterdata()->services();
        $this->assertApiRequest($this->lastApiRequest(), 'GET', '/v2/service', '');
        self::assertContainsOnlyInstancesOf(Service::class, $list);
        self::assertSame(ServiceType::CASHOUT, $list[0]->type);
        self::assertTrue($list[0]->isVerifiable);
        self::assertNotNull($list[0]->labelServiceNumber);
        self::assertSame('en', $list[0]->labelServiceNumber[0]->language);
    }

    public function testCashouts(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'cashout.json'));
        $list = $this->client->masterdata()->cashouts(serviceid: 999999);
        $this->assertApiRequest($this->lastApiRequest(), 'GET', '/v2/cashout', 'serviceid=999999');
        self::assertContainsOnlyInstancesOf(Cashout::class, $list);
        self::assertSame('PI-ENEO-PREPAID-001', $list[0]->payItemId);
    }

    public function testCashinsWithoutFilter(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'cashin.json'));
        $list = $this->client->masterdata()->cashins();
        $this->assertApiRequest($this->lastApiRequest(), 'GET', '/v2/cashin', '');
        self::assertContainsOnlyInstancesOf(Cashin::class, $list);
        self::assertSame('PI-MTNMOMO-DISBURSE-001', $list[0]->payItemId);
    }

    public function testTopups(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'topup.json'));
        $list = $this->client->masterdata()->topups(serviceid: 100200);
        $this->assertApiRequest($this->lastApiRequest(), 'GET', '/v2/topup', 'serviceid=100200');
        self::assertContainsOnlyInstancesOf(Topup::class, $list);
        self::assertCount(2, $list);
    }

    public function testProducts(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'product.json'));
        $list = $this->client->masterdata()->products(serviceid: 100400);
        $this->assertApiRequest($this->lastApiRequest(), 'GET', '/v2/product', 'serviceid=100400');
        self::assertContainsOnlyInstancesOf(Product::class, $list);
        self::assertSame(2500.0, $list[0]->amountLocalCur);
    }

    public function testVouchers(): void
    {
        $this->http->addResponse($this->jsonFixture(200, 'voucher.json'));
        $list = $this->client->masterdata()->vouchers(serviceid: 100300);
        $this->assertApiRequest($this->lastApiRequest(), 'GET', '/v2/voucher', 'serviceid=100300');
        self::assertSame('PI-GIFTCARD-1000', $list[0]->payItemId);
    }
}
