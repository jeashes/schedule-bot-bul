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
        parent::__construct();
    }

    public function create(string $displayName, ?string $description, ?string $name): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['displayName'] = $name;

        $query = '{+endpoint}?displayName={displayName}&';

        if (!empty($description)) {
            $params['description'] = $description;
            $query .= 'description={description}&';
        }

        if (!empty($name)) {
            $params['name'] = $name;
            $query .= 'name={name}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query . self::API_TOKEN_QUERY);
    }

    public function get(string $id): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $id;

        $query = '{+endpoint}/{id}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query . self::API_TOKEN_QUERY);
    }

    public function update(string $id): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $id;

        $query = '{+endpoint}/{id}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query . self::API_TOKEN_QUERY);
    }

    public function delete(string $id): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $id;

        $query = '{+endpoint}/{id}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query . self::API_TOKEN_QUERY);
    }

    private function prepareApiTokenParams(): array
    {
        return ['key' => $this->apiKey, 'token' => $this->apiToken, 'endpoint' => self::ORGANIZATION_URI];
    }
}
