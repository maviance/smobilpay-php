<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Model;

use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\Model\CollectionRequest;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class CollectionRequestTest extends TestCase
{
    private function uuid(): \Ramsey\Uuid\UuidInterface
    {
        return Uuid::fromString('0e1f7f4a-3b2c-4a8d-9d1f-1f5d2c3a4b6e');
    }

    public function testMinimalConstructionSucceeds(): void
    {
        $req = new CollectionRequest(
            quoteId: $this->uuid(),
            customerPhonenumber: '237699999999',
            customerEmailaddress: 'c@example.com',
        );
        self::assertSame('237699999999', $req->customerPhonenumber);
        self::assertNull($req->tag);
        self::assertNull($req->callbackUrl);
    }

    public function testTagAt50CharsIsAccepted(): void
    {
        $req = new CollectionRequest(
            quoteId: $this->uuid(),
            customerPhonenumber: '237699999999',
            customerEmailaddress: 'c@example.com',
            tag: \str_repeat('x', 50),
        );
        self::assertNotNull($req->tag);
        self::assertSame(50, \mb_strlen($req->tag));
    }

    public function testTagOver50CharsRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new CollectionRequest(
            quoteId: $this->uuid(),
            customerPhonenumber: '237699999999',
            customerEmailaddress: 'c@example.com',
            tag: \str_repeat('x', 51),
        );
    }

    public function testCallbackUrlAt255CharsIsAccepted(): void
    {
        $url = 'https://example.com/' . \str_repeat('a', 255 - 20);
        $req = new CollectionRequest(
            quoteId: $this->uuid(),
            customerPhonenumber: '237699999999',
            customerEmailaddress: 'c@example.com',
            callbackUrl: $url,
        );
        self::assertSame(255, \mb_strlen($req->callbackUrl ?? ''));
    }

    public function testCallbackUrlOver255CharsRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new CollectionRequest(
            quoteId: $this->uuid(),
            customerPhonenumber: '237699999999',
            customerEmailaddress: 'c@example.com',
            callbackUrl: 'https://example.com/' . \str_repeat('a', 256),
        );
    }

    public function testEmptyPhoneRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new CollectionRequest(
            quoteId: $this->uuid(),
            customerPhonenumber: '',
            customerEmailaddress: 'c@example.com',
        );
    }

    public function testEmptyEmailRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new CollectionRequest(
            quoteId: $this->uuid(),
            customerPhonenumber: '237699999999',
            customerEmailaddress: '',
        );
    }
}
