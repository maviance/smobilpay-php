<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit;

use Maviance\Smobilpay\Api\AccountValidationApi;
use Maviance\Smobilpay\Api\ConfirmApi;
use Maviance\Smobilpay\Api\InitiateApi;
use Maviance\Smobilpay\Api\MasterdataApi;
use Maviance\Smobilpay\Api\VerifyApi;
use Maviance\Smobilpay\Auth\OAuth2TokenManager;
use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\SmobilpayClient;
use Maviance\Smobilpay\SmobilpayConfig;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

final class SmobilpayClientTest extends TestCase
{
    private function baseConfig(): SmobilpayConfig
    {
        return new SmobilpayConfig(
            baseUrl: 'https://api.example.invalid',
            publicKey: 'pk',
            secretKey: 'sk',
        );
    }

    public function testCreateExposesFiveApiGroups(): void
    {
        $config = $this->baseConfig();
        $http = $this->createMock(ClientInterface::class);
        $factory = new Psr17Factory();
        $client = SmobilpayClient::create($config, $http, $factory, $factory);

        self::assertSame($config, $client->config());
        self::assertInstanceOf(MasterdataApi::class, $client->masterdata());
        self::assertInstanceOf(AccountValidationApi::class, $client->accountValidation());
        self::assertInstanceOf(InitiateApi::class, $client->initiate());
        self::assertInstanceOf(ConfirmApi::class, $client->confirm());
        self::assertInstanceOf(VerifyApi::class, $client->verify());
        self::assertInstanceOf(OAuth2TokenManager::class, $client->tokens());
    }

    public function testAccessorsReturnSameInstanceAcrossCalls(): void
    {
        $config = $this->baseConfig();
        $http = $this->createMock(ClientInterface::class);
        $factory = new Psr17Factory();
        $client = SmobilpayClient::create($config, $http, $factory, $factory);

        self::assertSame($client->masterdata(), $client->masterdata());
        self::assertSame($client->verify(), $client->verify());
        self::assertSame($client->tokens(), $client->tokens());
    }

    public function testFromConfigSucceedsWhenHttpAttachedToConfig(): void
    {
        $http = $this->createMock(ClientInterface::class);
        $factory = new Psr17Factory();
        $config = $this->baseConfig()->withHttp($http, $factory, $factory);
        $client = SmobilpayClient::fromConfig($config);
        self::assertInstanceOf(SmobilpayClient::class, $client);
    }

    public function testFromConfigThrowsWhenHttpClientMissing(): void
    {
        $this->expectException(SmobilpayConfigException::class);
        $this->expectExceptionMessageMatches('/httpClient/');
        SmobilpayClient::fromConfig($this->baseConfig());
    }

    public function testFromConfigThrowsWhenFactoriesMissing(): void
    {
        // Attach client but not factories — should still fail.
        $http = $this->createMock(ClientInterface::class);
        $factory = new Psr17Factory();
        // withHttp requires all three, so use the constructor directly to
        // simulate a partially-built config.
        $config = new SmobilpayConfig(
            baseUrl: 'https://api.example.invalid',
            publicKey: 'pk',
            secretKey: 'sk',
            httpClient: $http,
            // no requestFactory / streamFactory
        );
        $this->expectException(SmobilpayConfigException::class);
        $this->expectExceptionMessageMatches('/Factory/');
        SmobilpayClient::fromConfig($config);
    }
}
