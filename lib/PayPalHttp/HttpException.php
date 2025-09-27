<?php

namespace PayPalHttp;

class HttpException extends IOException {

    /**
     * @param string $message
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct($message, public $statusCode, public $headers) {
        parent::__construct($message);
    }
}
