<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Auth;

use DateInterval;
use DateTimeImmutable;
use Maviance\Smobilpay\Exception\SmobilpayAuthException;
use Maviance\Smobilpay\Exception\SmobilpayTransportException;
use Maviance\Smobilpay\Http\SystemClock;
use Maviance\Smobilpay\SmobilpayConfig;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException as PsrCacheException;
use Throwable;

/**
 * Mints, caches, and refreshes OAuth 2.0 access tokens via the Smobilpay
 * `POST /oauth/token` endpoint using the `client_credentials` grant.
 *
 * The token is cached in memory and (optionally) in a PSR-16 cache, and
 * reused until `now + tokenRefreshSkew >= expiresAt`, then a fresh one is
 * minted.
 *
 * Concurrency: PHP-FPM workers are single-threaded per request, so no in-
 * process lock is needed. A cross-process race (two workers minting at the
 * same time) is benign — both succeed, last write to the PSR-16 cache wins,
 * both tokens are valid until expiry.
 */
final class OAuth2TokenManager
{
    private const TOKEN_PATH = '/oauth/token';
    private const GRANT_BODY = 'grant_type=client_credentials';
    private const DEFAULT_TOKEN_TYPE = 'Bearer';
    private const CACHE_KEY_PREFIX = 'smobilpay.oauth2.token.';

    private ?OAuth2Token $current = null;
    private readonly ClockInterface $clock;
    private readonly string $cacheKey;

    public function __construct(
        private readonly SmobilpayConfig $config,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        ?ClockInterface $clock = null,
        private readonly ?CacheInterface $cache = null,
    ) {
        $this->clock = $clock ?? new SystemClock();
        $this->cacheKey = self::CACHE_KEY_PREFIX
            . \hash('sha256', $config->baseUrl . ':' . $config->publicKey);
    }

    /**
     * Returns a valid bearer token, minting one if the cache is empty or
     * the cached token is within `tokenRefreshSkew` of expiry.
     */
    public function accessToken(): string
    {
        $now = $this->clock->now();
        $skew = $this->config->tokenRefreshSkewInterval();

        if ($this->current !== null && !$this->current->isExpired($now, $skew)) {
            return $this->current->accessToken;
        }

        if ($this->cache !== null) {
            $fromCache = $this->readCache();
            if ($fromCache !== null && !$fromCache->isExpired($now, $skew)) {
                $this->current = $fromCache;

                return $fromCache->accessToken;
            }
        }

        return $this->mintAndStore();
    }

    /**
     * Forces a fresh mint, discarding any cached token (in-memory + PSR-16).
     */
    public function refresh(): string
    {
        $this->current = null;
        if ($this->cache !== null) {
            try {
                $this->cache->delete($this->cacheKey);
            } catch (PsrCacheException) {
                // ignore — we still mint a fresh one below
            }
        }

        return $this->mintAndStore();
    }

    /**
     * Currently cached token, if any. `null` until the first call to
     * {@see accessToken()}.
     */
    public function cachedToken(): ?OAuth2Token
    {
        return $this->current;
    }

    private function mintAndStore(): string
    {
        $issuedAt = $this->clock->now();
        $minted = $this->mintToken($issuedAt);
        $this->current = $minted;
        if ($this->cache !== null) {
            $ttl = $minted->expiresAt->getTimestamp() - $issuedAt->getTimestamp()
                - $this->config->tokenRefreshSkewSeconds;
            if ($ttl > 0) {
                try {
                    $this->cache->set($this->cacheKey, [
                        'accessToken' => $minted->accessToken,
                        'tokenType' => $minted->tokenType,
                        'expiresAt' => $minted->expiresAt->format(DATE_ATOM),
                    ], $ttl);
                } catch (PsrCacheException) {
                    // benign — in-memory cache still serves this process
                }
            }
        }

        return $minted->accessToken;
    }

    private function mintToken(DateTimeImmutable $issuedAt): OAuth2Token
    {
        $tokenUri = \rtrim($this->config->baseUrl, '/') . self::TOKEN_PATH;
        $request = $this->requestFactory->createRequest('POST', $tokenUri)
            ->withHeader('Authorization', 'Basic ' . $this->basicCredentials())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Accept', 'application/json')
            ->withBody($this->streamFactory->createStream(self::GRANT_BODY));

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new SmobilpayTransportException(
                'OAuth token request failed: ' . $e->getMessage(),
                $e,
            );
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status < 200 || $status >= 300) {
            $oauthError = $this->tryReadOAuthErrorCode($body);
            throw new SmobilpayAuthException(
                $status,
                $oauthError,
                \sprintf(
                    'OAuth token mint failed (HTTP %d)%s%s',
                    $status,
                    $oauthError !== null ? ", error={$oauthError}" : '',
                    $body === '' ? '' : ": {$body}",
                ),
            );
        }

        return $this->parseTokenResponse($body, $issuedAt);
    }

    private function parseTokenResponse(string $body, DateTimeImmutable $issuedAt): OAuth2Token
    {
        try {
            /** @var mixed $decoded */
            $decoded = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new SmobilpayAuthException(
                200,
                null,
                'OAuth token response was not valid JSON: ' . $e->getMessage(),
                $e,
            );
        }
        if (!\is_array($decoded)) {
            throw new SmobilpayAuthException(200, null,
                'OAuth token response did not decode to an object');
        }
        if (!isset($decoded['access_token']) || !\is_string($decoded['access_token'])
            || $decoded['access_token'] === '') {
            throw new SmobilpayAuthException(200, null,
                'OAuth token response missing required field "access_token"');
        }
        if (!isset($decoded['expires_in']) || !\is_int($decoded['expires_in'])) {
            // Some servers return expires_in as a string; allow both.
            if (isset($decoded['expires_in']) && \is_string($decoded['expires_in'])
                && \ctype_digit($decoded['expires_in'])) {
                $decoded['expires_in'] = (int) $decoded['expires_in'];
            } else {
                throw new SmobilpayAuthException(200, null,
                    'OAuth token response missing or invalid required field "expires_in"');
            }
        }
        $tokenType = (isset($decoded['token_type']) && \is_string($decoded['token_type'])
            && $decoded['token_type'] !== '')
            ? $decoded['token_type']
            : self::DEFAULT_TOKEN_TYPE;
        $expiresAt = $issuedAt->add(new DateInterval('PT' . $decoded['expires_in'] . 'S'));

        return new OAuth2Token($decoded['access_token'], $tokenType, $expiresAt);
    }

    private function readCache(): ?OAuth2Token
    {
        try {
            /** @var mixed $raw */
            $raw = $this->cache?->get($this->cacheKey);
        } catch (PsrCacheException) {
            return null;
        }
        if (!\is_array($raw) || !isset($raw['accessToken'], $raw['tokenType'], $raw['expiresAt'])
            || !\is_string($raw['accessToken'])
            || !\is_string($raw['tokenType'])
            || !\is_string($raw['expiresAt'])) {
            return null;
        }
        try {
            $expiresAt = new DateTimeImmutable($raw['expiresAt']);
        } catch (Throwable) {
            return null;
        }

        return new OAuth2Token($raw['accessToken'], $raw['tokenType'], $expiresAt);
    }

    private function tryReadOAuthErrorCode(string $body): ?string
    {
        if ($body === '') {
            return null;
        }
        try {
            /** @var mixed $decoded */
            $decoded = \json_decode($body, true, 16, \JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }
        if (\is_array($decoded) && isset($decoded['error']) && \is_string($decoded['error'])) {
            return $decoded['error'];
        }

        return null;
    }

    private function basicCredentials(): string
    {
        return \base64_encode($this->config->publicKey . ':' . $this->config->secretKey);
    }
}
