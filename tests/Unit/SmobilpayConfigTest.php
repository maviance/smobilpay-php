<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit;

use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\SmobilpayConfig;
use PHPUnit\Framework\TestCase;

final class SmobilpayConfigTest extends TestCase
{
    public function testDefaultsAreApplied(): void
    {
        $cfg = new SmobilpayConfig(
            baseUrl: 'https://api.example.invalid',
            publicKey: 'pk',
            secretKey: 'sk',
        );
        self::assertSame(SmobilpayConfig::DEFAULT_API_VERSION, $cfg->apiVersion);
        self::assertSame(SmobilpayConfig::DEFAULT_REQUEST_TIMEOUT_SECONDS, $cfg->requestTimeoutSeconds);
        self::assertSame(SmobilpayConfig::DEFAULT_TOKEN_REFRESH_SKEW_SECONDS, $cfg->tokenRefreshSkewSeconds);
        self::assertNull($cfg->tokenCache);
        self::assertNull($cfg->logger);
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

    public function testWithApiVersionReturnsNewInstance(): void
    {
        $cfg = new SmobilpayConfig(baseUrl: 'https://x', publicKey: 'pk', secretKey: 'sk');
        $bumped = $cfg->withApiVersion('3.1.0');
        self::assertSame('3.0.0', $cfg->apiVersion, 'Original config untouched');
        self::assertSame('3.1.0', $bumped->apiVersion);
        self::assertNotSame($cfg, $bumped);
    }

    public function testInvalidRequestTimeoutRejected(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        new SmobilpayConfig(
            baseUrl: 'https://x', publicKey: 'pk', secretKey: 'sk',
            requestTimeoutSeconds: 0,
        );
    }
}
