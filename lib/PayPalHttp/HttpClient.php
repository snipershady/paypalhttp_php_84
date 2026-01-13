<?php

namespace PayPalHttp;

/**
 * Class HttpClient
 * @package PayPalHttp
 *
 * Client used to make HTTP requests.
 */
class HttpClient
{
    /**
     * @var Curl
     */
    public $curl;

    /**
     * @var Environment
     */
    public $environment;

    /**
     * @var Injector[]
     */
    public $injectors = [];

    /**
     * @var Encoder
     */
    public $encoder;

    /**
     * HttpClient constructor. Pass the environment you wish to make calls to.
     *
     * @see Environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->encoder = new Encoder();
    }

    /**
     * Injectors are blocks that can be used for executing arbitrary pre-flight logic, such as modifying a request or logging data.
     * Executed in first-in first-out order.
     */
    public function addInjector(Injector $inj): void
    {
        $this->injectors[] = $inj;
    }

    /**
     * The method that takes an HTTP request, serializes the request, makes a call to given environment, and deserialize response
     *
     * @param HttpRequest $httpRequest
     * @return HttpResponse
     *
     * @throws HttpException
     * @throws IOException
     */
    public function execute(HttpRequest $httpRequest)
    {
        $requestCpy = clone $httpRequest;
        $curl = new Curl();

        foreach ($this->injectors as $inj) {
            $inj->inject($requestCpy);
        }

        $url = $this->environment->baseUrl() . $requestCpy->path;
        $formattedHeaders = $this->prepareHeaders($requestCpy->headers);
        if (!array_key_exists("user-agent", $formattedHeaders)) {
            $requestCpy->headers["user-agent"] = $this->userAgent();
        }

        $body = "";
        if (!is_null($requestCpy->body)) {
            $rawHeaders = $requestCpy->headers;
            $requestCpy->headers = $formattedHeaders;
            $body = $this->encoder->serializeRequest($requestCpy);
            $requestCpy->headers = $this->mapHeaders($rawHeaders, $requestCpy->headers);
        }

        $curl->setOpt(CURLOPT_URL, $url);
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, $requestCpy->verb);
        $curl->setOpt(CURLOPT_HTTPHEADER, $this->serializeHeaders($requestCpy->headers));
        $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOpt(CURLOPT_HEADER, 0);

        if (!is_null($requestCpy->body)) {
            $curl->setOpt(CURLOPT_POSTFIELDS, $body);
        }

        if (str_starts_with($this->environment->baseUrl(), "https://")) {
            $curl->setOpt(CURLOPT_SSL_VERIFYPEER, true);
            $curl->setOpt(CURLOPT_SSL_VERIFYHOST, 2);
        }

        if ($caCertPath = $this->getCACertFilePath()) {
            $curl->setOpt(CURLOPT_CAINFO, $caCertPath);
        }

        $response = $this->parseResponse($curl);
        $curl->close();

        return $response;
    }

    /**
     * Returns an array representing headers with their keys
     * to be lower case
     * @param $headers
     */
    public function prepareHeaders($headers): array
    {
        $preparedHeaders = array_change_key_case($headers);
        if (array_key_exists("content-type", $preparedHeaders)) {
            $preparedHeaders["content-type"] = strtolower((string) $preparedHeaders["content-type"]);
        }
        return $preparedHeaders;
    }

    /**
     * Returns an array representing headers with their key in
     * original cases and updated values
     * @param $rawHeaders
     * @param $formattedHeaders
     */
    public function mapHeaders(array $rawHeaders, array $formattedHeaders): array
    {
        $rawHeadersKey = array_keys($rawHeaders);
        foreach ($rawHeadersKey as $array_key) {
            if (array_key_exists(strtolower((string) $array_key), $formattedHeaders)) {
                $rawHeaders[$array_key] = $formattedHeaders[strtolower((string) $array_key)];
            }
        }
        return $rawHeaders;
    }

    /**
     * Returns default user-agent
     */
    public function userAgent(): string
    {
        return "PayPalHttp-PHP HTTP/1.1";
    }

    /**
     * Return the filepath to your custom CA Cert if needed.
     * @return string
     */
    protected function getCACertFilePath(): null
    {
        return null;
    }

    protected function setCurl(Curl $curl)
    {
        $this->curl = $curl;
    }

    protected function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @return list<\non-falsy-string>
     */
    private function serializeHeaders(array $headers): array
    {
        $headerArray = [];
        foreach ($headers as $key => $val) {
            $headerArray[] = $key . ": " . $val;
        }

        return $headerArray;
    }

    private function parseResponse(Curl $curl): HttpResponse
    {
        $headers = [];
        $curl->setOpt(
            CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use (&$headers): int {
                $len = strlen($header);

                $k = "";
                $v = "";

                $this->deserializeHeader($header, $k, $v);
                $headers[$k] = $v;

                return $len;
            }
        );

        $responseData = $curl->exec();
        $statusCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        $errorCode = $curl->errNo();
        $error = $curl->error();

        if ($errorCode > 0) {
            throw new IOException($error, $errorCode);
        }

        $body = $responseData;

        if ($statusCode >= 200 && $statusCode < 300) {
            $responseBody = null;

            if (!empty($body)) {
                $responseBody = $this->encoder->deserializeResponse($body, $this->prepareHeaders($headers));
            }

            return new HttpResponse(
                $errorCode === 0 ? $statusCode : $errorCode,
                $responseBody,
                $headers
            );
        } else {
            throw new HttpException($body, $statusCode, $headers);
        }
    }

    private function deserializeHeader($header, string &$key, string &$value): null
    {
        if ((string) $header !== '') {
            if (empty($header) || !str_contains((string) $header, ':')) {
                return null;
            }

            [$k, $v] = explode(":", (string) $header);
            $key = trim($k);
            $value = trim($v);
        }
        return null;
    }
}
