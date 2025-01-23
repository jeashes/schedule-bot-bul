<?php

namespace App\Service\Trello;

class TrelloConfig
{
    public readonly string $apiKey;
    public readonly string $apiToken;

    public function __construct(string $apiKey, string $apiToken)
    {
        $this->apiKey = $apiKey;
        $this->apiToken = $apiToken;
    }
}
