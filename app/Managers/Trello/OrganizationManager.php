<?php

namespace App\Managers\Trello;

use App\Clients\TrelloClient;
use App\Interfaces\Trello\OrganizationApiInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class OrganizationManager extends TrelloClient implements OrganizationApiInterface
{
    private const ORGANIZATION_URI = 'https://api.trello.com/1/organizations';

    public function __construct()
    {
        self::__construct();
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
            ->post('{+endpoint}?displayName={displayName}&' . self::API_TOKEN_QUERY);
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
            ->get('{+endpoint}/{id}?' . self::API_TOKEN_QUERY);
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
            ->put('{+endpoint}/{id}?' . self::API_TOKEN_QUERY);
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
            ->delete('{+endpoint}/{id}?' . self::API_TOKEN_QUERY);
    }
}
