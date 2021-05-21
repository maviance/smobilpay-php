<?php
/**
 * VerifyApi
 * PHP version 5
 *
 * @category Class
 * @package  Maviance\S3PApiClient
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * Smobilpay S3P API
 *
 * Smobilpay Third Party API FOR PAYMENT COLLECTIONS
 *
 * OpenAPI spec version: 3.0.2
 * 
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 * Swagger Codegen version: 3.0.24
 */
/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace Maviance\S3PApiClient\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Maviance\S3PApiClient\ApiException;
use Maviance\S3PApiClient\Configuration;
use Maviance\S3PApiClient\HeaderSelector;
use Maviance\S3PApiClient\ObjectSerializer;

/**
 * VerifyApi Class Doc Comment
 *
 * @category Class
 * @package  Maviance\S3PApiClient
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class VerifyApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     */
    public function __construct(
        ClientInterface $client = null,
        Configuration $config = null,
        HeaderSelector $selector = null
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: new Configuration();
        $this->headerSelector = $selector ?: new HeaderSelector();
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation historystdGet
     *
     * Retrieve list of historic payment collection.
     *
     * @param  string $xApiVersion api version info (required)
     * @param  \DateTime $timestampFrom Start date of transactions in result set (ISO 8601) (optional)
     * @param  \DateTime $timestampTo End date of transactions in result set (ISO 8601) (optional)
     *
     * @throws \Maviance\S3PApiClient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Maviance\S3PApiClient\Model\PaymentStatus[]
     */
    public function historystdGet($xApiVersion, $timestampFrom = null, $timestampTo = null)
    {
        list($response) = $this->historystdGetWithHttpInfo($xApiVersion, $timestampFrom, $timestampTo);
        return $response;
    }

    /**
     * Operation historystdGetWithHttpInfo
     *
     * Retrieve list of historic payment collection.
     *
     * @param  string $xApiVersion api version info (required)
     * @param  \DateTime $timestampFrom Start date of transactions in result set (ISO 8601) (optional)
     * @param  \DateTime $timestampTo End date of transactions in result set (ISO 8601) (optional)
     *
     * @throws \Maviance\S3PApiClient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \Maviance\S3PApiClient\Model\PaymentStatus[], HTTP status code, HTTP response headers (array of strings)
     */
    public function historystdGetWithHttpInfo($xApiVersion, $timestampFrom = null, $timestampTo = null)
    {
        $returnType = '\Maviance\S3PApiClient\Model\PaymentStatus[]';
        $request = $this->historystdGetRequest($xApiVersion, $timestampFrom, $timestampTo);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string','integer','bool'])) {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Maviance\S3PApiClient\Model\PaymentStatus[]',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 0:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Maviance\S3PApiClient\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation historystdGetAsync
     *
     * Retrieve list of historic payment collection.
     *
     * @param  string $xApiVersion api version info (required)
     * @param  \DateTime $timestampFrom Start date of transactions in result set (ISO 8601) (optional)
     * @param  \DateTime $timestampTo End date of transactions in result set (ISO 8601) (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function historystdGetAsync($xApiVersion, $timestampFrom = null, $timestampTo = null)
    {
        return $this->historystdGetAsyncWithHttpInfo($xApiVersion, $timestampFrom, $timestampTo)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation historystdGetAsyncWithHttpInfo
     *
     * Retrieve list of historic payment collection.
     *
     * @param  string $xApiVersion api version info (required)
     * @param  \DateTime $timestampFrom Start date of transactions in result set (ISO 8601) (optional)
     * @param  \DateTime $timestampTo End date of transactions in result set (ISO 8601) (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function historystdGetAsyncWithHttpInfo($xApiVersion, $timestampFrom = null, $timestampTo = null)
    {
        $returnType = '\Maviance\S3PApiClient\Model\PaymentStatus[]';
        $request = $this->historystdGetRequest($xApiVersion, $timestampFrom, $timestampTo);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'historystdGet'
     *
     * @param  string $xApiVersion api version info (required)
     * @param  \DateTime $timestampFrom Start date of transactions in result set (ISO 8601) (optional)
     * @param  \DateTime $timestampTo End date of transactions in result set (ISO 8601) (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function historystdGetRequest($xApiVersion, $timestampFrom = null, $timestampTo = null)
    {
        // verify the required parameter 'xApiVersion' is set
        if ($xApiVersion === null || (is_array($xApiVersion) && count($xApiVersion) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $xApiVersion when calling historystdGet'
            );
        }

        $resourcePath = '/historystd';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if ($timestampFrom !== null) {
            $queryParams['timestamp_from'] = ObjectSerializer::toQueryValue($timestampFrom, 'date');
        }
        // query params
        if ($timestampTo !== null) {
            $queryParams['timestamp_to'] = ObjectSerializer::toQueryValue($timestampTo, 'date');
        }
        // header params
        if ($xApiVersion !== null) {
            $headerParams['x-api-version'] = ObjectSerializer::toHeaderValue($xApiVersion);
        }


        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Operation verifytxGet
     *
     * Get the current payment collection status
     *
     * @param  string $xApiVersion api version info (required)
     * @param  string $ptn Unique payment collection transaction number (optional)
     * @param  string $trid custom external transaction reference provided during payment collection (optional)
     *
     * @throws \Maviance\S3PApiClient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return \Maviance\S3PApiClient\Model\PaymentStatus[]
     */
    public function verifytxGet($xApiVersion, $ptn = null, $trid = null)
    {
        list($response) = $this->verifytxGetWithHttpInfo($xApiVersion, $ptn, $trid);
        return $response;
    }

    /**
     * Operation verifytxGetWithHttpInfo
     *
     * Get the current payment collection status
     *
     * @param  string $xApiVersion api version info (required)
     * @param  string $ptn Unique payment collection transaction number (optional)
     * @param  string $trid custom external transaction reference provided during payment collection (optional)
     *
     * @throws \Maviance\S3PApiClient\ApiException on non-2xx response
     * @throws \InvalidArgumentException
     * @return array of \Maviance\S3PApiClient\Model\PaymentStatus[], HTTP status code, HTTP response headers (array of strings)
     */
    public function verifytxGetWithHttpInfo($xApiVersion, $ptn = null, $trid = null)
    {
        $returnType = '\Maviance\S3PApiClient\Model\PaymentStatus[]';
        $request = $this->verifytxGetRequest($xApiVersion, $ptn, $trid);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null
                );
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            $responseBody = $response->getBody();
            if ($returnType === '\SplFileObject') {
                $content = $responseBody; //stream goes to serializer
            } else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string','integer','bool'])) {
                    $content = json_decode($content);
                }
            }

            return [
                ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders()
            ];

        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Maviance\S3PApiClient\Model\PaymentStatus[]',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
                case 0:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Maviance\S3PApiClient\Model\Error',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    /**
     * Operation verifytxGetAsync
     *
     * Get the current payment collection status
     *
     * @param  string $xApiVersion api version info (required)
     * @param  string $ptn Unique payment collection transaction number (optional)
     * @param  string $trid custom external transaction reference provided during payment collection (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function verifytxGetAsync($xApiVersion, $ptn = null, $trid = null)
    {
        return $this->verifytxGetAsyncWithHttpInfo($xApiVersion, $ptn, $trid)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation verifytxGetAsyncWithHttpInfo
     *
     * Get the current payment collection status
     *
     * @param  string $xApiVersion api version info (required)
     * @param  string $ptn Unique payment collection transaction number (optional)
     * @param  string $trid custom external transaction reference provided during payment collection (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function verifytxGetAsyncWithHttpInfo($xApiVersion, $ptn = null, $trid = null)
    {
        $returnType = '\Maviance\S3PApiClient\Model\PaymentStatus[]';
        $request = $this->verifytxGetRequest($xApiVersion, $ptn, $trid);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    $responseBody = $response->getBody();
                    if ($returnType === '\SplFileObject') {
                        $content = $responseBody; //stream goes to serializer
                    } else {
                        $content = $responseBody->getContents();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'verifytxGet'
     *
     * @param  string $xApiVersion api version info (required)
     * @param  string $ptn Unique payment collection transaction number (optional)
     * @param  string $trid custom external transaction reference provided during payment collection (optional)
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function verifytxGetRequest($xApiVersion, $ptn = null, $trid = null)
    {
        // verify the required parameter 'xApiVersion' is set
        if ($xApiVersion === null || (is_array($xApiVersion) && count($xApiVersion) === 0)) {
            throw new \InvalidArgumentException(
                'Missing the required parameter $xApiVersion when calling verifytxGet'
            );
        }

        $resourcePath = '/verifytx';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if ($ptn !== null) {
            $queryParams['ptn'] = ObjectSerializer::toQueryValue($ptn, null);
        }
        // query params
        if ($trid !== null) {
            $queryParams['trid'] = ObjectSerializer::toQueryValue($trid, null);
        }
        // header params
        if ($xApiVersion !== null) {
            $headerParams['x-api-version'] = ObjectSerializer::toHeaderValue($xApiVersion);
        }


        // body params
        $_tempBody = null;

        if ($multipart) {
            $headers = $this->headerSelector->selectHeadersForMultipart(
                ['application/json']
            );
        } else {
            $headers = $this->headerSelector->selectHeaders(
                ['application/json'],
                []
            );
        }

        // for model (json/xml)
        if (isset($_tempBody)) {
            // $_tempBody is the method argument, if present
            $httpBody = $_tempBody;
            // \stdClass has no __toString(), so we should encode it manually
            if ($httpBody instanceof \stdClass && $headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($httpBody);
            }
        } elseif (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $multipartContents[] = [
                        'name' => $formParamName,
                        'contents' => $formParamValue
                    ];
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif ($headers['Content-Type'] === 'application/json') {
                $httpBody = \GuzzleHttp\json_encode($formParams);

            } else {
                // for HTTP post (form)
                $httpBody = \GuzzleHttp\Psr7\build_query($formParams);
            }
        }


        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $query = \GuzzleHttp\Psr7\build_query($queryParams);
        return new Request(
            'GET',
            $this->config->getHost() . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        return $options;
    }
}
