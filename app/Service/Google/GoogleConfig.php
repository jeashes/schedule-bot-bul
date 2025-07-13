<?php

namespace App\Service\Google;

class GoogleConfig
{
    public readonly string $apiKey;

    public readonly string $cx;

    public function __construct(string $apiKey, string $cx)
    {
        $this->apiKey = $apiKey;
        $this->cx = $cx;
    }
}
