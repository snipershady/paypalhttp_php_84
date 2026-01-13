<?php

namespace PayPalHttp;

/**
 * Class HttpRequest
 * @package PayPalHttp
 *
 * Request object that holds all the necessary information required by HTTPClient
 *
 * @see HttpClient
 */
class HttpRequest
{
    /**
     * @var array | string
     */
    public $body;

    /**
     * @var array
     */
    public $headers;

    /**
     * @param string $path
     * @param string $verb
     */
    public function __construct(public $path, public $verb)
    {
        $this->body = null;
        $this->headers = [];
    }
}
