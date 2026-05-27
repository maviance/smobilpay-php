<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Auth;

use DateInterval;
use DateTimeImmutable;
use Maviance\Smobilpay\Auth\OAuth2Token;
use PHPUnit\Framework\TestCase;

final class OAuth2TokenTest extends TestCase
{
    public function testNotExpiredWhenNowFarBeforeExpiry(): void
    {
        $token = new OAuth2Token('jwt', 'Bearer', new DateTimeImmutable('2026-05-02T10:00:00Z'));
        $now = new DateTimeImmutable('2026-05-02T09:00:00Z');
        self::assertFalse($token->isExpired($now, new DateInterval('PT30S')));
    }

    public function testExpiredWhenNowAfterExpiry(): void
    {
        $token = new OAuth2Token('jwt', 'Bearer', new DateTimeImmutable('2026-05-02T10:00:00Z'));
        $now = new DateTimeImmutable('2026-05-02T10:00:01Z');
        self::assertTrue($token->isExpired($now, new DateInterval('PT0S')));
    }

    public function testExpiredWhenWithinSkewWindow(): void
    {
        $token = new OAuth2Token('jwt', 'Bearer', new DateTimeImmutable('2026-05-02T10:00:00Z'));
        $now = new DateTimeImmutable('2026-05-02T09:59:31Z');
        self::assertTrue(
            $token->isExpired($now, new DateInterval('PT30S')),
            'now + 30s skew is at expiry, should be considered expired',
        );
    }

    public function testNotExpiredJustOutsideSkewWindow(): void
    {
        $token = new OAuth2Token('jwt', 'Bearer', new DateTimeImmutable('2026-05-02T10:00:00Z'));
        $now = new DateTimeImmutable('2026-05-02T09:59:29Z');
        self::assertFalse(
            $token->isExpired($now, new DateInterval('PT30S')),
            'now + 30s = 09:59:59 < expiry 10:00:00 — fresh',
        );
    }

    public function testFieldsAreImmutableAndExposed(): void
    {
        $expiry = new DateTimeImmutable('2026-05-02T10:00:00Z');
        $token = new OAuth2Token('abc.def.ghi', 'Bearer', $expiry);
        self::assertSame('abc.def.ghi', $token->accessToken);
        self::assertSame('Bearer', $token->tokenType);
        self::assertSame($expiry, $token->expiresAt);
    }
}
