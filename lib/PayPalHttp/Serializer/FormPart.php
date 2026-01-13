<?php

namespace PayPalHttp\Serializer;

class FormPart
{
    private readonly array $headers;

    public function __construct(private $value, $headers)
    {
        $this->headers = array_merge([], $headers);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
