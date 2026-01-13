<?php

namespace PayPalHttp\Serializer;

use Exception;
use Override;
use PayPalHttp\HttpRequest;
use PayPalHttp\Serializer;

class Form implements Serializer
{
    /**
     * @return string Regex that matches the content type it supports.
     */
    #[Override]
    public function contentType(): string
    {
        return "/^application\/x-www-form-urlencoded$/";
    }

    /**
     * @return string representation of your data after being serialized.
     */
    #[Override]
    public function encode(HttpRequest $request): string
    {
        if (!is_array($request->body) || !$this->isAssociative($request->body)) {
            throw new Exception("HttpRequest body must be an associative array when Content-Type is: " . $request->headers["Content-Type"]);
        }

        return http_build_query($request->body);
    }

    /**
     * @param $body
     * @throws Exception as multipart does not support deserialization.
     */
    #[Override]
    public function decode($body): never
    {
        throw new Exception("CurlSupported does not support deserialization");
    }

    private function isAssociative(array $array): bool
    {
        return array_values($array) !== $array;
    }
}
