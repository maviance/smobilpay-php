<?php

declare(strict_types=1);

namespace Maviance\Smobilpay;

use Maviance\Smobilpay\Api\AccountValidationApi;
use Maviance\Smobilpay\Api\ConfirmApi;
use Maviance\Smobilpay\Api\InitiateApi;
use Maviance\Smobilpay\Api\MasterdataApi;
use Maviance\Smobilpay\Api\VerifyApi;
use Maviance\Smobilpay\Auth\OAuth2TokenManager;
use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Maviance\Smobilpay\Http\HttpTransport;
use Maviance\Smobilpay\Http\JsonSerializer;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Top-level entry point for the Smobilpay partner API.
 *
 * Construct with a {@see SmobilpayConfig}; the client lazily mints an
 * OAuth 2.0 bearer token on the first authenticated request and caches it
 * (in memory and optionally in a PSR-16 cache).
 *
 * ```php
 * $config = new SmobilpayConfig(
 *     baseUrl:   'https://api.example.invalid',
 *     publicKey: getenv('SMOBILPAY_PUBLIC_KEY'),
 *     secretKey: getenv('SMOBILPAY_SECRET_KEY'),
 * );
 *
 * // Pass your own PSR-18 client + PSR-17 factories — Guzzle satisfies both.
 * $http     = new \GuzzleHttp\Client();
 * $factory  = new \GuzzleHttp\Psr7\HttpFactory();
 * $client   = SmobilpayClient::create($config, $http, $factory, $factory);
 *
 * $pong = $client->verify()->ping();
 * ```
 *
 * Reusable and safe to keep alive for the lifetime of the application.
 */
final class SmobilpayClient
{
    private readonly OAuth2TokenManager $tokenManager;
    private readonly HttpTransport $transport;
    private readonly MasterdataApi $masterdata;
    private readonly AccountValidationApi $accountValidation;
    private readonly InitiateApi $initiate;
    private readonly ConfirmApi $confirm;
    private readonly VerifyApi $verify;

    public function __construct(
        private readonly SmobilpayConfig $config,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ) {
        $serializer = new JsonSerializer();
        $this->tokenManager = new OAuth2TokenManager(
            $config,
            $httpClient,
            $requestFactory,
            $streamFactory,
            $config->clock,
            $config->tokenCache,
        );
        $this->transport = new HttpTransport(
            $config,
            $httpClient,
            $requestFactory,
            $streamFactory,
            $this->tokenManager,
            $serializer,
            $config->logger,
        );
        $this->masterdata = new MasterdataApi($this->transport);
        $this->accountValidation = new AccountValidationApi($this->transport);
        $this->initiate = new InitiateApi($this->transport);
        $this->confirm = new ConfirmApi($this->transport);
        $this->verify = new VerifyApi($this->transport);
    }

    /**
     * Convenience constructor when PSR-18 + PSR-17 are already attached to
     * the config via {@see SmobilpayConfig::withHttp()} or constructor
     * arguments. Throws if none were supplied.
     */
    public static function fromConfig(SmobilpayConfig $config): self
    {
        if ($config->httpClient === null) {
            throw new SmobilpayConfigException(
                'SmobilpayConfig has no PSR-18 httpClient. Either pass one via the constructor '
                . 'or use SmobilpayClient::create(...) to attach one explicitly.',
            );
        }
        if ($config->requestFactory === null || $config->streamFactory === null) {
            throw new SmobilpayConfigException(
                'SmobilpayConfig has no PSR-17 requestFactory/streamFactory. Either pass them '
                . 'via the constructor or use SmobilpayClient::create(...) to attach them explicitly.',
            );
        }

        return new self(
            $config,
            $config->httpClient,
            $config->requestFactory,
            $config->streamFactory,
        );
    }

    /**
     * Explicit-injection constructor. Most partners use this — pass your
     * own PSR-18 client and PSR-17 factories. Guzzle's `HttpFactory`
     * satisfies both `RequestFactoryInterface` and `StreamFactoryInterface`,
     * so two args is the common pattern:
     *
     * ```php
     * $factory = new \GuzzleHttp\Psr7\HttpFactory();
     * $client  = SmobilpayClient::create($config, new \GuzzleHttp\Client(), $factory, $factory);
     * ```
     */
    public static function create(
        SmobilpayConfig $config,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ): self {
        return new self($config, $httpClient, $requestFactory, $streamFactory);
    }

    public function config(): SmobilpayConfig
    {
        return $this->config;
    }

    public function tokens(): OAuth2TokenManager
    {
        return $this->tokenManager;
    }

    public function masterdata(): MasterdataApi
    {
        return $this->masterdata;
    }

    public function accountValidation(): AccountValidationApi
    {
        return $this->accountValidation;
    }

    public function initiate(): InitiateApi
    {
        return $this->initiate;
    }

    public function confirm(): ConfirmApi
    {
        return $this->confirm;
    }

    public function verify(): VerifyApi
    {
        return $this->verify;
    }
}
