<?php

namespace App\Clients;

class TrelloClient
{
    protected string $apiKey;
    protected string $apiToken;

    public function __construct()
    {
        $this->apiKey = config('trello.api_key');
        $this->apiToken = config('trello.api_token');
    }
}
