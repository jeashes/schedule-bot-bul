<?php

namespace App\Managers\Trello;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class OrganizationManager
{
    private const ORGANIZATION_URI = 'https://api.trello.com/1/organizations';

    private string $apiKey;
    private string $apiToken;

    public function __construct()
    {
        $this->apiKey = config('trello.api_key');
        $this->apiToken = config('trello.api_token');
    }

    public function create(string $name): Response
    {
        return Http::withUrlParameters([
            'endpoint' => self::ORGANIZATION_URI,
            'displayName' => $name,
            'key' => $this->apiKey,
            'token' => $this->apiToken,
            ])
            ->withHeaders($this->prepareHeaders())
            ->post('{+endpoint}?displayName={displayName}&key={key}&token={token}');
    }

    public function get(string $id): Response
    {
        return Http::withUrlParameters([
            'endpoint' => self::ORGANIZATION_URI,
            'id' => $id,
            'key' => $this->apiKey,
            'token' => $this->apiToken,
            ])
            ->withHeaders($this->prepareHeaders())
            ->get('{+endpoint}/{id}?key={key}&token={token}');
    }

    public function update(string $id): Response
    {
        return Http::withUrlParameters([
            'endpoint' => self::ORGANIZATION_URI,
            'id' => $id,
            'key' => $this->apiKey,
            'token' => $this->apiToken,
            ])
            ->withHeaders($this->prepareHeaders())
            ->put('{+endpoint}/{id}?key={key}&token={token}');
    }

    public function delete(string $id): Response
    {
        return Http::withUrlParameters([
            'endpoint' => self::ORGANIZATION_URI,
            'id' => $id,
            'key' => $this->apiKey,
            'token' => $this->apiToken,
            ])
            ->withHeaders($this->prepareHeaders())
            ->delete('{+endpoint}/{id}?key={key}&token={token}');
    }

    private function prepareHeaders(): array
    {
        return ['Accept' => 'application/json'];
    }
}
