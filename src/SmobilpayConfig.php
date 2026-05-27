<?php

declare(strict_types=1);

namespace Maviance\Smobilpay;

use DateInterval;
use Maviance\Smobilpay\Exception\SmobilpayConfigException;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Immutable configuration for a {@see SmobilpayClient}.
 *
 * Construct with named arguments — `baseUrl`, `publicKey`, and `secretKey`
 * are required; everything else has a sensible default. The PSR-18
 * HTTP client + PSR-17 factories are required at the time the client mints
 * its first request; if you don't pass them here, you can pass them to
 * {@see SmobilpayClient::create()} instead.
 *
 * ```php
 * $config = new SmobilpayConfig(
 *     baseUrl:    'https://api.example.invalid',
 *     publicKey:  $_ENV['SMOBILPAY_PUBLIC_KEY'],
 *     secretKey:  $_ENV['SMOBILPAY_SECRET_KEY'],
 *     tokenCache: new \Symfony\Component\Cache\Psr16Cache($pool),
 * );
 * ```
 *
 * Returns a new instance from any `with*()` method — config is fully
 * immutable.
 */
final readonly class SmobilpayConfig
{
    /** Default value sent as the `x-api-version` header on every secured request. */
    public const DEFAULT_API_VERSION = '3.0.0';

    /** Default per-request timeout in seconds. */
    public const DEFAULT_REQUEST_TIMEOUT_SECONDS = 30;

    /** Default refresh-ahead window for OAuth tokens in seconds. */
    public const DEFAULT_TOKEN_REFRESH_SKEW_SECONDS = 30;

    public function __construct(
        public string $baseUrl,
        public string $publicKey,
        public string $secretKey,
        public string $apiVersion = self::DEFAULT_API_VERSION,
        public int $requestTimeoutSeconds = self::DEFAULT_REQUEST_TIMEOUT_SECONDS,
        public int $tokenRefreshSkewSeconds = self::DEFAULT_TOKEN_REFRESH_SKEW_SECONDS,
        public ?ClientInterface $httpClient = null,
        public ?RequestFactoryInterface $requestFactory = null,
        public ?StreamFactoryInterface $streamFactory = null,
        public ?CacheInterface $tokenCache = null,
        public ?LoggerInterface $logger = null,
        public ?ClockInterface $clock = null,
    ) {
        if ($baseUrl === '') {
            throw new SmobilpayConfigException('SmobilpayConfig: baseUrl is required');
        }
        if (!\preg_match('#^https?://#i', $baseUrl)) {
            throw new SmobilpayConfigException(\sprintf(
                'SmobilpayConfig: baseUrl must start with http:// or https://, got "%s"',
                $baseUrl,
            ));
        }
        if ($publicKey === '') {
            throw new SmobilpayConfigException('SmobilpayConfig: publicKey is required');
        }
        if ($secretKey === '') {
            throw new SmobilpayConfigException('SmobilpayConfig: secretKey is required');
        }
        if ($apiVersion === '') {
            throw new SmobilpayConfigException('SmobilpayConfig: apiVersion must not be empty');
        }
        if ($requestTimeoutSeconds < 1) {
            throw new SmobilpayConfigException(\sprintf(
                'SmobilpayConfig: requestTimeoutSeconds must be >= 1, got %d',
                $requestTimeoutSeconds,
            ));
        }
        if ($tokenRefreshSkewSeconds < 0) {
            throw new SmobilpayConfigException(\sprintf(
                'SmobilpayConfig: tokenRefreshSkewSeconds must be >= 0, got %d',
                $tokenRefreshSkewSeconds,
            ));
        }
    }

    public function tokenRefreshSkewInterval(): DateInterval
    {
        return new DateInterval('PT' . $this->tokenRefreshSkewSeconds . 'S');
    }

    public function requestTimeoutInterval(): DateInterval
    {
        return new DateInterval('PT' . $this->requestTimeoutSeconds . 'S');
    }

    /**
     * Return a copy with the supplied PSR-18 client + PSR-17 factories.
     * Useful when you want to declare the config statically and inject
     * transport at construction time.
     */
    public function withHttp(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ): self {
        return new self(
            baseUrl: $this->baseUrl,
            publicKey: $this->publicKey,
            secretKey: $this->secretKey,
            apiVersion: $this->apiVersion,
            requestTimeoutSeconds: $this->requestTimeoutSeconds,
            tokenRefreshSkewSeconds: $this->tokenRefreshSkewSeconds,
            httpClient: $httpClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
            tokenCache: $this->tokenCache,
            logger: $this->logger,
            clock: $this->clock,
        );
    }

    public function withTokenCache(CacheInterface $cache): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            publicKey: $this->publicKey,
            secretKey: $this->secretKey,
            apiVersion: $this->apiVersion,
            requestTimeoutSeconds: $this->requestTimeoutSeconds,
            tokenRefreshSkewSeconds: $this->tokenRefreshSkewSeconds,
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            streamFactory: $this->streamFactory,
            tokenCache: $cache,
            logger: $this->logger,
            clock: $this->clock,
        );
    }

    public function withLogger(LoggerInterface $logger): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            publicKey: $this->publicKey,
            secretKey: $this->secretKey,
            apiVersion: $this->apiVersion,
            requestTimeoutSeconds: $this->requestTimeoutSeconds,
            tokenRefreshSkewSeconds: $this->tokenRefreshSkewSeconds,
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            streamFactory: $this->streamFactory,
            tokenCache: $this->tokenCache,
            logger: $logger,
            clock: $this->clock,
        );
    }

    public function withClock(ClockInterface $clock): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            publicKey: $this->publicKey,
            secretKey: $this->secretKey,
            apiVersion: $this->apiVersion,
            requestTimeoutSeconds: $this->requestTimeoutSeconds,
            tokenRefreshSkewSeconds: $this->tokenRefreshSkewSeconds,
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            streamFactory: $this->streamFactory,
            tokenCache: $this->tokenCache,
            logger: $this->logger,
            clock: $clock,
        );
    }

    public function withApiVersion(string $apiVersion): self
    {
        return new self(
            baseUrl: $this->baseUrl,
            publicKey: $this->publicKey,
            secretKey: $this->secretKey,
            apiVersion: $apiVersion,
            requestTimeoutSeconds: $this->requestTimeoutSeconds,
            tokenRefreshSkewSeconds: $this->tokenRefreshSkewSeconds,
            httpClient: $this->httpClient,
            requestFactory: $this->requestFactory,
            streamFactory: $this->streamFactory,
            tokenCache: $this->tokenCache,
            logger: $this->logger,
            clock: $this->clock,
        );
    }
}
