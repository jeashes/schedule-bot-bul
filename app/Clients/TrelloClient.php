<?php

namespace App\Clients;

class TrelloClient
{
    protected string $apiKey;
    protected string $apiToken;

    protected const API_TOKEN_QUERY = 'key={key}&token={token}';

    public function __construct()
    {
        $this->apiKey = config('trello.api_key');
        $this->apiToken = config('trello.api_token');
    }

    protected function prepareHeaders(): array
    {
        return ['Accept' => 'application/json'];
    }
}
