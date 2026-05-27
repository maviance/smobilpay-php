<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Tests\Integration;

use Http\Mock\Client as MockHttpClient;
use Maviance\Smobilpay\SmobilpayClient;
use Maviance\Smobilpay\SmobilpayConfig;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * Base class for per-API integration tests.
 *
 * Spins up a {@see SmobilpayClient} backed by a `php-http/mock-client`
 * PSR-18 client preloaded with an OAuth token mint response (so the first
 * API call always succeeds at the auth step), plus whatever fixture
 * responses each test queues up.
 *
 * Tests assert two things:
 *  1. The PSR-7 request the client *sent* (method, URI, headers, body).
 *  2. The hydrated response object matches the fixture's expected values.
 */
abstract class ApiTestCase extends TestCase
{
    protected MockHttpClient $http;
    protected SmobilpayClient $client;
    protected Psr17Factory $factory;
    protected SmobilpayConfig $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->http = new MockHttpClient();
        $this->factory = new Psr17Factory();
        $this->config = new SmobilpayConfig(
            baseUrl: 'https://api.example.invalid',
            publicKey: 'public-key-abcdef',
            secretKey: 'secret-key-fedcba',
        );
        // Pre-load the OAuth mint so the first API call passes auth.
        $this->http->addResponse($this->jsonFixture(200, 'oauth-token.json'));
        $this->client = SmobilpayClient::create(
            $this->config,
            $this->http,
            $this->factory,
            $this->factory,
        );
    }

    protected function jsonFixture(int $status, string $filename): Response
    {
        $path = __DIR__ . '/../Fixtures/' . $filename;
        if (!\is_file($path)) {
            throw new \RuntimeException("Fixture not found: {$filename}");
        }
        $body = \file_get_contents($path);
        if ($body === false) {
            throw new \RuntimeException("Failed to read fixture: {$filename}");
        }

        return new Response(
            $status,
            ['Content-Type' => 'application/json'],
            $body,
        );
    }

    /**
     * Returns the API request (the 2nd request — index 1 — since the first
     * is the OAuth token mint preloaded in setUp). Override or extend if a
     * test makes multiple API calls.
     */
    protected function lastApiRequest(): RequestInterface
    {
        $requests = $this->http->getRequests();
        $count = \count($requests);
        if ($count < 2) {
            self::fail('Expected at least one API request after OAuth mint; got ' . $count);
        }

        return $requests[$count - 1];
    }

    protected function assertApiRequest(
        RequestInterface $req,
        string $method,
        string $expectedPath,
        ?string $expectedQuery = null,
    ): void {
        self::assertSame($method, $req->getMethod());
        $uri = $req->getUri();
        self::assertSame($expectedPath, $uri->getPath());
        if ($expectedQuery !== null) {
            self::assertSame($expectedQuery, $uri->getQuery());
        }
        self::assertSame('3.0.0', $req->getHeaderLine('x-api-version'));
        self::assertStringStartsWith('Bearer ', $req->getHeaderLine('Authorization'));
        self::assertSame('application/json', $req->getHeaderLine('Accept'));
    }
}
