<?php

namespace App\Service\Trello\CheckLists;

use App\Service\Trello\BaseClient;
use Illuminate\Http\Client\Response;
use App\Service\Trello\TrelloConfig;
use Illuminate\Support\Facades\Http;

class CheckListClient extends BaseClient
{
    private const CARDS_URI = 'https://api.trello.com/1/checklists';

    public function __construct(TrelloConfig $config)
    {
        parent::__construct($config->apiKey, $config->apiToken);
    }

    public function createCheckItem(string $idCheckList, string $name): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCheckList;
        $params['name'] = $name;

        $query = '{+endpoint}/{id}/checkItems?name={name}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query . self::API_TOKEN_QUERY);
    }

    private function prepareApiTokenParams(): array
    {
        return ['key' => $this->apiKey, 'token' => $this->apiToken, 'endpoint' => self::CARDS_URI];
    }
}
