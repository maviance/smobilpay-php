<?php

declare(strict_types=1);

namespace Maviance\S3PApiClient;

/**
 * ApiClient Class Doc Comment
 *
 * @category Class
 *
 * @author   Swagger Codegen team
 *
 * @link     https://github.com/swagger-api/swagger-codegen
 */

use GuzzleHttp\Client;
use GuzzleHttp\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\Uuid;

class ApiClient extends Client
{
    public function __construct(private readonly string $token, private readonly string $secret, array $config = [])
    {
        parent::__construct($config);
    }

    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        $options['headers'] = ['Authorization' => $this->buildAuthorizationHeader($request)];

        return parent::send($request, $options);
    }

    /** Build S3P Authorization Header */
    public function buildAuthorizationHeader(RequestInterface $request): string
    {
        $data = [];
        if ($request->getMethod() == 'POST') {
            $data = Utils::jsonDecode($request->getBody()->getContents(), true);
        }
        $auth_titleKey = 's3pAuth';
        $auth_tokenKey = 's3pAuth_token';
        $auth_nonceKey = 's3pAuth_nonce';
        $auth_signatureKey = 's3pAuth_signature';
        $auth_signatureMethodKey = 's3pAuth_signature_method';
        $auth_timestampKey = 's3pAuth_timestamp';
        $separator = ', ';
        $signature_method = 'HMAC-SHA1';
        $timestamp = time();
        $nonce = Uuid::uuid4()->toString();
        $params =
            [
                's3pAuth_nonce' => $nonce,
                's3pAuth_timestamp' => $timestamp,
                's3pAuth_signature_method' => $signature_method,
                's3pAuth_token' => $this->token,
            ];

        // disect GET request query parameters
        if (!empty($request->getUri()->getQuery())) {
            $queryparameter = explode('&', urldecode($request->getUri()->getQuery()));
            foreach ($queryparameter as $v) {
                $item = explode('=', $v);
                $params[$item[0]] = $item[1];
            }
        }
        $sig = new HMACSignature(
            $request->getMethod(),
            $this->getUrl($request->getUri()),
            array_merge($data, $params)
        );
        $signature = $sig->generate($this->secret);

        return $auth_titleKey . ' ' .
            $auth_timestampKey . '="' . $timestamp . '"' . $separator .
            $auth_signatureKey . '="' . $signature . '"' . $separator .
            $auth_nonceKey . '="' . $nonce . '"' . $separator .
            $auth_signatureMethodKey . '="' . $signature_method . '"' . $separator .
            $auth_tokenKey . '="' . $this->token . '"';
    }

    /** Build url to use in signature validation */
    private function getUrl(UriInterface $uri): string
    {
        // in case ports are not standard -> add to url
        return implode(
            '',
            [
                $uri->getScheme(),
                '://',
                $uri->getHost(),
                is_null($uri->getPort()) ? '' : ((!in_array($uri->getPort(), [80, 443])) ? ':' . $uri->getPort() : ''),
                $uri->getPath(),
            ]
        );
    }
}
