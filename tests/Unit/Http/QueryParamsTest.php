<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Http;

use DateTimeImmutable;
use Maviance\Smobilpay\Http\QueryParams;
use Maviance\Smobilpay\Model\ServiceType;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class QueryParamsTest extends TestCase
{
    public function testEmpty(): void
    {
        $q = QueryParams::of();
        self::assertTrue($q->isEmpty());
        self::assertSame('', $q->encode());
    }

    public function testNullsAreSkipped(): void
    {
        $q = QueryParams::of()
            ->add('a', 'x')
            ->add('b', null)
            ->add('c', 1);
        self::assertSame('a=x&c=1', $q->encode());
    }

    public function testInsertionOrderPreserved(): void
    {
        $q = QueryParams::of()
            ->add('zeta', 1)
            ->add('alpha', 2)
            ->add('mu', 3);
        self::assertSame('zeta=1&alpha=2&mu=3', $q->encode());
    }

    public function testRawUrlEncodingSpacesAndReserved(): void
    {
        $q = QueryParams::of()
            ->add('q', 'a b+c&d=e')
            ->add('s', 'naïve');
        self::assertStringContainsString('q=a%20b%2Bc%26d%3De', $q->encode());
        self::assertStringContainsString('s=na%C3%AFve', $q->encode());
    }

    public function testBoolEncodesAsString(): void
    {
        $q = QueryParams::of()->add('on', true)->add('off', false);
        self::assertSame('on=true&off=false', $q->encode());
    }

    public function testEnumEncodesAsValue(): void
    {
        $q = QueryParams::of()->add('type', ServiceType::CASHOUT);
        self::assertSame('type=CASHOUT', $q->encode());
    }

    public function testDateTimeEncodesAsAtom(): void
    {
        $dt = new DateTimeImmutable('2026-05-02T08:30:00+00:00');
        $q = QueryParams::of()->add('from', $dt);
        self::assertSame('from=2026-05-02T08%3A30%3A00%2B00%3A00', $q->encode());
    }

    public function testUuidEncodesViaStringable(): void
    {
        $uuid = Uuid::fromString('0e1f7f4a-3b2c-4a8d-9d1f-1f5d2c3a4b6e');
        $q = QueryParams::of()->add('quoteId', $uuid);
        self::assertSame('quoteId=0e1f7f4a-3b2c-4a8d-9d1f-1f5d2c3a4b6e', $q->encode());
    }
}
