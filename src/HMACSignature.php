<?php

namespace Maviance\S3PApiClient;

class HMACSignature
{
    /**
     * This method generates the signature based on given parameters
     *
     * @param array $parameters A list of parameters used for generation
     *
     * @return string
     */
    public function generate($secret)
    {
        $encodedString = hash_hmac('sha1', $this->getBaseString(), $secret, true);
        return base64_encode($encodedString);
    }

    /**
     * @var
     */
    private $method;
    /**
     * @var
     */
    private $url;
    /**
     * @var array
     */
    private $params;

    /**
     * @param       $method HTTP method
     * @param       $url    URL
     * @param array $params parameters to include in signature
     */
    public function __construct($method, $url, array $params)
    {
        $this->method = $method;
        $this->url = $url;
        $this->params = $params;
    }

    /**
     * @param string $signature
     * @param array $parameters
     *
     * @return bool
     */
    public function verify($signature, $secret)
    {
        if ($signature !== $this->generate($secret)) {
            throw new \Exception("Signature Does Not Match");
        }

        return true;
    }

    /**
     * compile base string
     *
     * @return string
     */
    public function getBaseString()
    {
        $glue = "&";
        $sorted = $this->getParameterString();

        return
            // capitalize httptype
            strtoupper(trim($this->method)) . $glue .
            // urlencode url
            rawurlencode(trim($this->url)) . $glue .
            // lexically sorted parameter string
            $sorted;
    }

    /**
     * Prepares a string to be signed
     *
     * @param array $parameters List of signature fields
     *
     * @return string
     */
    protected function getParameterString()
    {
        $glue = "&";
        $stringToBeSigned = '';
        // lexically sort parameters
        ksort($this->params);
        foreach ($this->params as $key => $value) {
            $stringToBeSigned .= trim($key) . '=' . trim($value) . $glue;
        }

        // urlencode and remove trailing glue
        return rawurlencode(trim(substr($stringToBeSigned, 0, -1)));
    }
}
