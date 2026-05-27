<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit;

use DateTimeImmutable;
use DateTimeZone;
use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\Http\SystemClock;
use Maviance\Smobilpay\SmobilpayConfig;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class SmobilpayConfigTest extends TestCase
{
    private function base(): SmobilpayConfig
    {
        return new SmobilpayConfig(baseUrl: 'https://api.example.invalid', publicKey: 'pk', secretKey: 'sk');
    }

    public function testDefaultsAreApplied(): void
    {
        $cfg = $this->base();
        self::assertSame(SmobilpayConfig::DEFAULT_API_VERSION, $cfg->apiVersion);
        self::assertSame(SmobilpayConfig::DEFAULT_REQUEST_TIMEOUT_SECONDS, $cfg->requestTimeoutSeconds);
        self::assertSame(SmobilpayConfig::DEFAULT_TOKEN_REFRESH_SKEW_SECONDS, $cfg->tokenRefreshSkewSeconds);
        self::assertNull($cfg->tokenCache);
        self::assertNull($cfg->logger);
        self::assertNull($cfg->httpClient);
        self::assertNull($cfg->requestFactory);
        self::assertNull($cfg->streamFactory);
        self::assertNull($cfg->clock);
    }

    public function testEmptyBaseUrlRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new SmobilpayConfig(baseUrl: '', publicKey: 'pk', secretKey: 'sk');
    }

    public function testNonHttpBaseUrlRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new SmobilpayConfig(baseUrl: 'ftp://api.example.invalid', publicKey: 'pk', secretKey: 'sk');
    }

    public function testEmptyPublicKeyRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new SmobilpayConfig(baseUrl: 'https://x', publicKey: '', secretKey: 'sk');
    }

    public function testEmptySecretKeyRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new SmobilpayConfig(baseUrl: 'https://x', publicKey: 'pk', secretKey: '');
    }

    public function testEmptyApiVersionRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new SmobilpayConfig(baseUrl: 'https://x', publicKey: 'pk', secretKey: 'sk', apiVersion: '');
    }

    public function testInvalidRequestTimeoutRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new SmobilpayConfig(
            baseUrl: 'https://x',
            publicKey: 'pk',
            secretKey: 'sk',
            requestTimeoutSeconds: 0,
        );
    }

    public function testNegativeTokenRefreshSkewRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new SmobilpayConfig(
            baseUrl: 'https://x',
            publicKey: 'pk',
            secretKey: 'sk',
            tokenRefreshSkewSeconds: -1,
        );
    }

    public function testTokenRefreshSkewIntervalSeconds(): void
    {
        $cfg = $this->base();
        $interval = $cfg->tokenRefreshSkewInterval();
        self::assertSame(30, $interval->s);
    }

    public function testRequestTimeoutInterval(): void
    {
        $cfg = $this->base();
        $interval = $cfg->requestTimeoutInterval();
        self::assertSame(30, $interval->s);
    }

    public function testWithApiVersionReturnsNewInstance(): void
    {
        $cfg = $this->base();
        $bumped = $cfg->withApiVersion('3.1.0');
        self::assertSame('3.0.0', $cfg->apiVersion);
        self::assertSame('3.1.0', $bumped->apiVersion);
        self::assertNotSame($cfg, $bumped);
    }

    public function testWithTokenCache(): void
    {
        $cfg = $this->base();
        $cache = new Psr16Cache(new ArrayAdapter());
        $derived = $cfg->withTokenCache($cache);
        self::assertNull($cfg->tokenCache);
        self::assertSame($cache, $derived->tokenCache);
        self::assertNotSame($cfg, $derived);
    }

    public function testWithLogger(): void
    {
        $cfg = $this->base();
        $logger = new NullLogger();
        $derived = $cfg->withLogger($logger);
        self::assertNull($cfg->logger);
        self::assertSame($logger, $derived->logger);
    }

    public function testWithClock(): void
    {
        $cfg = $this->base();
        $clock = new SystemClock();
        $derived = $cfg->withClock($clock);
        self::assertNull($cfg->clock);
        self::assertSame($clock, $derived->clock);
    }

    public function testWithHttp(): void
    {
        $cfg = $this->base();
        $http = $this->createMock(ClientInterface::class);
        $factory = new Psr17Factory();
        $derived = $cfg->withHttp($http, $factory, $factory);
        self::assertNull($cfg->httpClient);
        self::assertSame($http, $derived->httpClient);
        self::assertSame($factory, $derived->requestFactory);
        self::assertSame($factory, $derived->streamFactory);
    }

    public function testSystemClockReturnsUtc(): void
    {
        // Sanity check on the bundled clock — ensures it ends up in UTC.
        $clock = new SystemClock();
        $now = $clock->now();
        self::assertSame('UTC', $now->getTimezone()->getName());
        self::assertEqualsWithDelta((new DateTimeImmutable('now', new DateTimeZone('UTC')))->getTimestamp(), $now->getTimestamp(), 2);
        unset($clock);
        $this->assertInstanceOf(ClockInterface::class, new SystemClock());
    }
}
