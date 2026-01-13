<?php

namespace PayPalHttp\Serializer;

use Override;
use PayPalHttp\HttpRequest;
use PayPalHttp\Serializer;

/**
 * Class Text
 * @package PayPalHttp\Serializer
 *
 * Serializer for Text content types.
 */
class Text implements Serializer
{
    #[Override]
    public function contentType(): string
    {
        return "/^text\\/.*/";
    }

    #[Override]
    public function encode(HttpRequest $request)
    {
        $body = $request->body;
        if (is_string($body)) {
            return $body;
        }
        if (is_array($body)) {
            return json_encode($body);
        }
        return implode(" ", $body);
    }

    public function decode($data)
    {
        return $data;
    }
}
