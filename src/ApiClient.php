<?php
namespace Maviance\S3PApiClient;

/**
 * ApiClient Class Doc Comment
 *
 * @category Class
 * @package  maviance\S3PApiClient
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\Uuid;

class ApiClient extends Client
{
    /**
     * @var
     */
    private $token;
    /**
     * @var
     */
    private $secret;

    public function __construct($token, $secret, array $config = [])
    {
        $this->token = $token;
        $this->secret = $secret;
        parent::__construct($config);
    }

    public function send(RequestInterface $request, array $options = [])
    {
        $options['headers'] = ["Authorization" => $this->buildAuthorizationHeader($request)];
        return parent::send($request, $options);
    }

    /**
     * Build S3P Authorization Header
     * @param $token
     * @param $secret
     * @param $http
     * @param $url
     * @param $data
     * @return string
     */
    public function buildAuthorizationHeader(RequestInterface $request)
    {
        $data = [];
        if ($request->getMethod() == "POST") {
            $data = \GuzzleHttp\json_decode($request->getBody()->getContents(), true);
        }
        $auth_titleKey = "s3pAuth";
        $auth_tokenKey = "s3pAuth_token";
        $auth_nonceKey = "s3pAuth_nonce";
        $auth_signatureKey = "s3pAuth_signature";
        $auth_signatureMethodKey = "s3pAuth_signature_method";
        $auth_timestampKey = "s3pAuth_timestamp";
        $separator = ", ";
        $signature_method = "HMAC-SHA1";
        $timestamp = time();
        $nonce = Uuid::uuid4()->toString();
        $params =
            array(
                "s3pAuth_nonce" => $nonce,
                "s3pAuth_timestamp" => $timestamp,
                "s3pAuth_signature_method" => $signature_method,
                "s3pAuth_token" => $this->token,
            );

        // disect GET request query parameters
        if (!empty($request->getUri()->getQuery())) {
            $queryparameter = explode("&", urldecode($request->getUri()->getQuery()));
            foreach ($queryparameter as $k => $v) {
                $item = explode("=", $v);
                $params[$item[0]] = $item[1];
            }
        }
        $sig = new HMACSignature(
            $request->getMethod(),
            $this->getUrl($request->getUri()),
            array_merge($data, $params)
        );
        $signature = $sig->generate($this->secret);
        $v =
            $auth_titleKey . " " .
            $auth_timestampKey . "=\"" . $timestamp . "\"" . $separator .
            $auth_signatureKey . "=\"" . $signature . "\"" . $separator .
            $auth_nonceKey . "=\"" . $nonce . "\"" . $separator .
            $auth_signatureMethodKey . "=\"" . $signature_method . "\"" . $separator .
            $auth_tokenKey . "=\"" . $this->token . "\"";

        return $v;
    }

    /**
     * Build url to use in signature validation
     * @param Uri $uri
     * @return string
     */
    private function getUrl(Uri $uri)
    {
        // in case ports are not standard -> add to url
        return implode(
            '',
            [
                $uri->getScheme(),
                "://",
                $uri->getHost(),
                is_null($uri->getPort()) ? "" : (!in_array($uri->getPort(), [80, 443])) ? ":" . $uri->getPort() : "",
                $uri->getPath()
            ]
        );
    }
}
