<?php

namespace PayPalHttp;

/**
 * Class Curl
 * @package PayPalHttp
 *
 * Curl wrapper used by HttpClient to make curl requests.
 * @see HttpClient
 */
class Curl
{
    protected $curl;

    public function __construct($curl = null)
    {

        if (empty($curl)) {
            $curl = curl_init();
        }
        $this->curl = $curl;
    }

    public function setOpt($option, $value): static
    {
        curl_setopt($this->curl, $option, $value);
        return $this;
    }

    public function close(): static
    {
        return $this;
    }

    public function exec(): bool|string
    {
        return curl_exec($this->curl);
    }

    public function errNo(): int
    {
        return curl_errno($this->curl);
    }

    public function getInfo($option): array|false
    {
        return curl_getinfo($this->curl, $option);
    }

    public function error(): string
    {
        return curl_error($this->curl);
    }
}
