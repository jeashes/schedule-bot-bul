<?php

namespace App\Service\Google;

class BaseClient
{
    protected readonly string $apiKey;

    protected readonly string $cx;

    public function __construct(string $apiKey, string $cx)
    {
        $this->apiKey = $apiKey;
        $this->cx = $cx;
    }

    protected function prepareHeaders(): array
    {
        return ['Accept' => 'application/json'];
    }
}
