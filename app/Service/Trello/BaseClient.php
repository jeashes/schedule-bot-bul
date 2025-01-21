<?php

namespace App\Service\Trello;

class BaseClient
{
    protected readonly string $apiKey;
    protected readonly string $apiToken;

    protected const API_TOKEN_QUERY = 'key={key}&token={token}';

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
