<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Unit\Auth;

use DateTimeImmutable;
use Http\Mock\Client as MockHttpClient;
use Maviance\Smobilpay\Auth\OAuth2TokenManager;
use Maviance\Smobilpay\Exception\SmobilpayAuthException;
use Maviance\Smobilpay\SmobilpayConfig;
use Maviance\Smobilpay\Tests\Unit\FakeClock;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class OAuth2TokenManagerTest extends TestCase
{
    private function makeConfig(): SmobilpayConfig
    {
        return new SmobilpayConfig(
            baseUrl: 'https://api.example.invalid',
            publicKey: 'public-key-abcdef',
            secretKey: 'secret-key-fedcba',
        );
    }

    private function tokenResponse(int $expiresIn = 3600, ?string $body = null): Response
    {
        $body ??= json_encode([
            'access_token' => 'JWT-' . bin2hex(random_bytes(8)),
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn,
        ]);
        \assert(\is_string($body));

        return new Response(200, ['Content-Type' => 'application/json'], $body);
    }

    public function testFirstCallMintsToken(): void
    {
        $http = new MockHttpClient();
        $http->addResponse($this->tokenResponse());
        $factory = new Psr17Factory();
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        $mgr = new OAuth2TokenManager($this->makeConfig(), $http, $factory, $factory, $clock);
        $bearer = $mgr->accessToken();

        self::assertStringStartsWith('JWT-', $bearer);
        $req = $http->getLastRequest();
        self::assertNotNull($req);
        self::assertSame('POST', $req->getMethod());
        self::assertStringEndsWith('/oauth/token', (string) $req->getUri());
        self::assertSame('grant_type=client_credentials', (string) $req->getBody());
        // RFC 6749 §4.4.2 — S3P /oauth/token rejects a non-form content type
        // (MPAY-30022); pin the header so a refactor can't drift to JSON.
        self::assertSame('application/x-www-form-urlencoded', $req->getHeaderLine('Content-Type'));
        $authHeader = $req->getHeaderLine('Authorization');
        self::assertStringStartsWith('Basic ', $authHeader);
        $decoded = base64_decode(substr($authHeader, 6), true);
        self::assertSame('public-key-abcdef:secret-key-fedcba', $decoded);
    }

    public function testSecondCallWithinTtlReusesCachedToken(): void
    {
        $http = new MockHttpClient();
        $http->addResponse($this->tokenResponse());
        $factory = new Psr17Factory();
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        $mgr = new OAuth2TokenManager($this->makeConfig(), $http, $factory, $factory, $clock);
        $first = $mgr->accessToken();
        $clock->advance('PT5M'); // 5 min later, still inside 1h TTL minus 30s skew
        $second = $mgr->accessToken();

        self::assertSame($first, $second);
        // Only one HTTP call should have happened.
        self::assertSame(1, \count($http->getRequests()));
    }

    public function testWithinSkewWindowTriggersReMint(): void
    {
        $http = new MockHttpClient();
        $http->addResponse($this->tokenResponse());
        $http->addResponse($this->tokenResponse());
        $factory = new Psr17Factory();
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        $mgr = new OAuth2TokenManager($this->makeConfig(), $http, $factory, $factory, $clock);
        $first = $mgr->accessToken();
        // Token expires at 09:00:00. Skew is 30s. Advance to 08:59:35.
        $clock->advance('PT59M35S');
        $second = $mgr->accessToken();

        self::assertNotSame($first, $second, 'Should mint fresh inside skew window');
        self::assertSame(2, \count($http->getRequests()));
    }

    public function testRefreshForcesReMintEvenWhenCacheFresh(): void
    {
        $http = new MockHttpClient();
        $http->addResponse($this->tokenResponse());
        $http->addResponse($this->tokenResponse());
        $factory = new Psr17Factory();
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        $mgr = new OAuth2TokenManager($this->makeConfig(), $http, $factory, $factory, $clock);
        $first = $mgr->accessToken();
        $second = $mgr->refresh();
        self::assertNotSame($first, $second);
        self::assertSame(2, \count($http->getRequests()));
    }

    public function testPsr16CacheShortCircuitsAcrossManagerInstances(): void
    {
        $http = new MockHttpClient();
        $http->addResponse($this->tokenResponse());
        $factory = new Psr17Factory();
        $cache = new Psr16Cache(new ArrayAdapter());
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));
        $config = $this->makeConfig()->withTokenCache($cache);

        // First manager mints + caches.
        $a = new OAuth2TokenManager($config, $http, $factory, $factory, $clock, $cache);
        $first = $a->accessToken();
        self::assertSame(1, \count($http->getRequests()));

        // Second manager (fresh in-memory) hits the PSR-16 cache, no HTTP.
        $b = new OAuth2TokenManager($config, $http, $factory, $factory, $clock, $cache);
        $second = $b->accessToken();
        self::assertSame($first, $second);
        self::assertSame(1, \count($http->getRequests()), 'Second manager should NOT make a fresh HTTP call');
    }

    public function testHttpErrorBubblesAsAuthException(): void
    {
        $http = new MockHttpClient();
        $http->addResponse(new Response(
            401,
            ['Content-Type' => 'application/json'],
            '{"error":"invalid_client","error_description":"Bad creds."}',
        ));
        $factory = new Psr17Factory();
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        $mgr = new OAuth2TokenManager($this->makeConfig(), $http, $factory, $factory, $clock);
        try {
            $mgr->accessToken();
            self::fail('Expected SmobilpayAuthException');
        } catch (SmobilpayAuthException $e) {
            self::assertSame(401, $e->httpStatus());
            self::assertSame('invalid_client', $e->oauthError());
        }
    }

    public function testMissingAccessTokenThrows(): void
    {
        $http = new MockHttpClient();
        $http->addResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{"token_type":"Bearer","expires_in":3600}',
        ));
        $factory = new Psr17Factory();
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        $mgr = new OAuth2TokenManager($this->makeConfig(), $http, $factory, $factory, $clock);
        $this->expectException(SmobilpayAuthException::class);
        $mgr->accessToken();
    }

    public function testMissingExpiresInThrows(): void
    {
        $http = new MockHttpClient();
        $http->addResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{"access_token":"abc","token_type":"Bearer"}',
        ));
        $factory = new Psr17Factory();
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        $mgr = new OAuth2TokenManager($this->makeConfig(), $http, $factory, $factory, $clock);
        $this->expectException(SmobilpayAuthException::class);
        $mgr->accessToken();
    }

    public function testStringExpiresInIsAccepted(): void
    {
        $http = new MockHttpClient();
        $http->addResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{"access_token":"abc","token_type":"Bearer","expires_in":"3600"}',
        ));
        $factory = new Psr17Factory();
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        $mgr = new OAuth2TokenManager($this->makeConfig(), $http, $factory, $factory, $clock);
        self::assertSame('abc', $mgr->accessToken());
    }

    public function testMalformedJsonThrows(): void
    {
        $http = new MockHttpClient();
        $http->addResponse(new Response(200, ['Content-Type' => 'application/json'], 'not-json'));
        $factory = new Psr17Factory();
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        $mgr = new OAuth2TokenManager($this->makeConfig(), $http, $factory, $factory, $clock);
        $this->expectException(SmobilpayAuthException::class);
        $mgr->accessToken();
    }

    public function testHttp502BubblesWithStatus(): void
    {
        $http = new MockHttpClient();
        $http->addResponse(new Response(502, [], 'Bad Gateway'));
        $factory = new Psr17Factory();
        $clock = new FakeClock(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        $mgr = new OAuth2TokenManager($this->makeConfig(), $http, $factory, $factory, $clock);
        try {
            $mgr->accessToken();
            self::fail('Expected SmobilpayAuthException');
        } catch (SmobilpayAuthException $e) {
            self::assertSame(502, $e->httpStatus());
        }
    }
}
