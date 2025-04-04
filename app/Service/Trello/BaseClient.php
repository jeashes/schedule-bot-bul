<?php

namespace App\Service\Trello;

class BaseClient
{
    protected const API_TOKEN_QUERY = 'key={key}&token={token}';

    protected readonly string $apiKey;

    protected readonly string $apiToken;

    public function __construct(string $apiKey, string $apiToken)
    {
        $this->apiKey = $apiKey;
        $this->apiToken = $apiToken;
    }

    protected function prepareHeaders(): array
    {
        return ['Accept' => 'application/json'];
    }
}
