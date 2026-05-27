<?php

declare(strict_types=1);

namespace Maviance\Smobilpay\Http;

use Maviance\Smobilpay\Auth\OAuth2TokenManager;
use Maviance\Smobilpay\Exception\SmobilpayApiException;
use Maviance\Smobilpay\Exception\SmobilpayTransportException;
use Maviance\Smobilpay\Model\ApiError;
use Maviance\Smobilpay\SmobilpayConfig;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Request/response engine for the Smobilpay client.
 *
 * For each authenticated call:
 *  1. Resolves the URI from `baseUrl + path + query`.
 *  2. Asks the {@see OAuth2TokenManager} for a fresh bearer token
 *     (cached between calls).
 *  3. Attaches `Authorization: Bearer`, `x-api-version`, `Accept:
 *     application/json` headers (+ `Content-Type` on POST).
 *  4. Dispatches via the PSR-18 client and decodes the response body.
 *  5. On non-2xx, parses the body as {@see ApiError} and throws
 *     {@see SmobilpayApiException}.
 */
final class HttpTransport
{
    private const HEADER_AUTHORIZATION = 'Authorization';
    private const HEADER_API_VERSION = 'x-api-version';
    private const HEADER_ACCEPT = 'Accept';
    private const HEADER_CONTENT_TYPE = 'Content-Type';
    private const CONTENT_TYPE_JSON = 'application/json';

    public function __construct(
        private readonly SmobilpayConfig $config,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly OAuth2TokenManager $tokenManager,
        private readonly JsonSerializer $serializer,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Authenticated GET. `$type` is either a class name to hydrate a single
     * object, or `[ClassName::class]` to hydrate a list of that class.
     *
     * @template T of object
     * @param class-string<T>|array{0: class-string<T>} $type
     * @return T|list<T>
     */
    public function get(string $path, QueryParams $query, string|array $type): mixed
    {
        $request = $this->buildAuthedRequest('GET', $path, $query);
        $body = $this->execute($request);

        return $this->serializer->decode($body, $type);
    }

    /**
     * Authenticated GET that returns the raw response body. Used by
     * endpoints whose response shape is a bare JSON primitive
     * (currently only `GET /v2/verify`, which returns `true`/`false`).
     */
    public function getRaw(string $path, QueryParams $query): string
    {
        $request = $this->buildAuthedRequest('GET', $path, $query);

        return $this->execute($request);
    }

    /**
     * Authenticated POST with a JSON body.
     *
     * @template T of object
     * @param class-string<T>|array{0: class-string<T>} $type
     * @return T|list<T>
     */
    public function post(string $path, object $body, string|array $type): mixed
    {
        $json = $this->serializer->encode($body);
        $request = $this->buildAuthedRequest('POST', $path, QueryParams::of())
            ->withHeader(self::HEADER_CONTENT_TYPE, self::CONTENT_TYPE_JSON)
            ->withBody($this->streamFactory->createStream($json));
        $responseBody = $this->execute($request);

        return $this->serializer->decode($responseBody, $type);
    }

    private function buildAuthedRequest(string $method, string $path, QueryParams $query): \Psr\Http\Message\RequestInterface
    {
        $uri = $this->resolveUri($path, $query);
        $bearer = $this->tokenManager->accessToken();

        return $this->requestFactory->createRequest($method, $uri)
            ->withHeader(self::HEADER_AUTHORIZATION, 'Bearer ' . $bearer)
            ->withHeader(self::HEADER_API_VERSION, $this->config->apiVersion)
            ->withHeader(self::HEADER_ACCEPT, self::CONTENT_TYPE_JSON);
    }

    private function resolveUri(string $path, QueryParams $query): string
    {
        $base = rtrim($this->config->baseUrl, '/');
        $p = str_starts_with($path, '/') ? $path : '/' . $path;
        $uri = $base . $p;
        if (!$query->isEmpty()) {
            $uri .= '?' . $query->encode();
        }

        return $uri;
    }

    private function execute(\Psr\Http\Message\RequestInterface $request): string
    {
        $this->logger?->debug('smobilpay.http.request', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
        ]);
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $this->logger?->warning('smobilpay.http.transport_error', ['error' => $e->getMessage()]);

            throw new SmobilpayTransportException(
                'HTTP transport error: ' . $e->getMessage(),
                $e,
            );
        }
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        $this->logger?->debug('smobilpay.http.response', [
            'status' => $status,
            'body_len' => \strlen($body),
        ]);
        if ($status >= 200 && $status < 300) {
            return $body;
        }
        $error = $this->tryParseError($body);

        throw new SmobilpayApiException($status, $error, $body);
    }

    private function tryParseError(string $body): ?ApiError
    {
        if (trim($body) === '') {
            return null;
        }
        try {
            $parsed = $this->serializer->decode($body, ApiError::class);
        } catch (Throwable) {
            return null;
        }

        return $parsed instanceof ApiError ? $parsed : null;
    }
}
