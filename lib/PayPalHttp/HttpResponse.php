<?php

namespace PayPalHttp;

/**
 * Class HttpResponse
 * @package PayPalHttp
 *
 * Object that holds your response details
 */
class HttpResponse {

    /**
     * @param int $statusCode
     * @param mixed[]|string|object $body
     * @param mixed[] $headers
     */
    public function __construct(public $statusCode, public $result, public $headers) {
        
    }
}
