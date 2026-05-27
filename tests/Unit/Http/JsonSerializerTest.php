<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Http;

use Maviance\Smobilpay\Exception\SmobilpayParseException;
use Maviance\Smobilpay\Http\JsonSerializer;
use Maviance\Smobilpay\Model\AmountType;
use Maviance\Smobilpay\Model\ApiError;
use Maviance\Smobilpay\Model\CollectionRequest;
use Maviance\Smobilpay\Model\Merchant;
use Maviance\Smobilpay\Model\Ping;
use Maviance\Smobilpay\Model\QuoteRequest;
use Maviance\Smobilpay\Model\Service;
use Maviance\Smobilpay\Model\ServiceStatus;
use Maviance\Smobilpay\Model\ServiceType;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class JsonSerializerTest extends TestCase
{
    private function s(): JsonSerializer
    {
        return new JsonSerializer();
    }

    public function testDecodeSingleObject(): void
    {
        $json = '{"time":"2026-05-02T08:30:00+00:00","version":"3.0.0","nonce":"nx","key":"K"}';
        /** @var Ping $p */
        $p = $this->s()->decode($json, Ping::class);
        self::assertInstanceOf(Ping::class, $p);
        self::assertSame('3.0.0', $p->version);
        self::assertSame('nx', $p->nonce);
        self::assertSame('K', $p->key);
        self::assertSame('2026-05-02', $p->time->format('Y-m-d'));
    }

    public function testDecodeList(): void
    {
        $json = '[{"merchant":"ENEO","name":"Eneo","description":null,"country":"CMR","status":"Active"}]';
        $list = $this->s()->decode($json, [Merchant::class]);
        self::assertIsArray($list);
        self::assertCount(1, $list);
        self::assertInstanceOf(Merchant::class, $list[0]);
        self::assertSame('ENEO', $list[0]->merchant);
    }

    public function testDecodeEnumField(): void
    {
        $json = json_encode([
            'serviceid' => 1, 'merchant' => 'ENEO', 'title' => 't',
            'description' => null, 'category' => null,
            'country' => 'CMR', 'localCur' => 'XAF',
            'type' => 'CASHOUT', 'status' => 'Active',
        ]);
        /** @var Service $svc */
        $svc = $this->s()->decode((string) $json, Service::class);
        self::assertSame(ServiceType::CASHOUT, $svc->type);
        self::assertSame(ServiceStatus::Active, $svc->status);
    }

    public function testDecodeUnknownEnumValueThrows(): void
    {
        $json = json_encode([
            'serviceid' => 1, 'merchant' => 'ENEO', 'title' => 't',
            'description' => null, 'category' => null,
            'country' => 'CMR', 'localCur' => 'XAF',
            'type' => 'NOT_A_REAL_TYPE', 'status' => 'Active',
        ]);
        $this->expectException(SmobilpayParseException::class);
        $this->s()->decode((string) $json, Service::class);
    }

    public function testDecodeUnknownJsonKeysIgnored(): void
    {
        $json = '{"respCode":42,"devMsg":"x","usrMsg":null,"link":null,"unknownExtra":"ignored"}';
        /** @var ApiError $e */
        $e = $this->s()->decode($json, ApiError::class);
        self::assertSame(42, $e->respCode);
        self::assertSame('x', $e->devMsg);
    }

    public function testDecodeMissingRequiredThrows(): void
    {
        $this->expectException(SmobilpayParseException::class);
        // ApiError requires respCode
        $this->s()->decode('{"devMsg":"x"}', ApiError::class);
    }

    public function testEncodeSkipsNulls(): void
    {
        $req = new QuoteRequest(5000, 'PI-X');
        $json = $this->s()->encode($req);
        self::assertSame('{"amount":5000,"payItemId":"PI-X"}', $json);
    }

    public function testEncodeUuidAsString(): void
    {
        $req = new CollectionRequest(
            quoteId: Uuid::fromString('0e1f7f4a-3b2c-4a8d-9d1f-1f5d2c3a4b6e'),
            customerPhonenumber: '237699999999',
            customerEmailaddress: 'c@example.com',
        );
        $json = $this->s()->encode($req);
        self::assertStringContainsString('"quoteId":"0e1f7f4a-3b2c-4a8d-9d1f-1f5d2c3a4b6e"', $json);
        self::assertStringContainsString('"customerPhonenumber":"237699999999"', $json);
        // Optional null fields skipped
        self::assertStringNotContainsString('"customerName"', $json);
        self::assertStringNotContainsString('"tag"', $json);
    }

    public function testDecodeNestedListOf(): void
    {
        $json = json_encode([
            'serviceid' => 1, 'merchant' => 'ENEO', 'title' => 't',
            'description' => null, 'category' => null,
            'country' => 'CMR', 'localCur' => 'XAF',
            'type' => 'CASHOUT', 'status' => 'Active',
            'labelServiceNumber' => [
                ['language' => 'en', 'localText' => 'Meter number'],
                ['language' => 'fr', 'localText' => 'Compteur'],
            ],
        ]);
        /** @var Service $svc */
        $svc = $this->s()->decode((string) $json, Service::class);
        self::assertIsArray($svc->labelServiceNumber);
        self::assertCount(2, $svc->labelServiceNumber);
        self::assertSame('en', $svc->labelServiceNumber[0]->language);
        self::assertSame('Meter number', $svc->labelServiceNumber[0]->localText);
    }

    public function testEncodeEnum(): void
    {
        // AmountType inside an object — exercised via Cashout for instance,
        // but here we use a minimal object to check the principle.
        $obj = new class (AmountType::FIXED) {
            public function __construct(public readonly AmountType $amountType)
            {
            }
        };
        $json = $this->s()->encode($obj);
        self::assertSame('{"amountType":"FIXED"}', $json);
    }

    public function testDecodeMalformedJsonThrows(): void
    {
        $this->expectException(SmobilpayParseException::class);
        $this->s()->decode('not-json', ApiError::class);
    }

    public function testDecodeEmptyBodyThrows(): void
    {
        $this->expectException(SmobilpayParseException::class);
        $this->s()->decode('', ApiError::class);
    }
}
