<?php

namespace App\Service\Trello\CheckLists;

use App\Interfaces\Trello\ChecklistsApiInterface;
use App\Service\Trello\BaseClient;
use App\Service\Trello\TrelloConfig;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CheckListClient extends BaseClient implements ChecklistsApiInterface
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
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function createChecklist(
        string $idCard, ?string $name = null,
        ?string $pos = null, ?string $idChecklistSource = null
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['idCard'] = $idCard;
        $query = '{+endpoint}?idCard={idCard}&';

        if (! empty($name)) {
            $params['name'] = $name;
            $query .= 'name={name}&';
        }

        if (! empty($pos)) {
            $params['pos'] = $pos;
            $query .= 'pos={post}&';
        }

        if (! empty($idChecklistSource)) {
            $params['idChecklistSource'] = $idChecklistSource;
            $query .= 'idChecklistSource={idChecklistSource}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function getChecklist(
        string $idChecklist, ?string $cards = null,
        ?string $checkItems = null, ?string $checkItemsFields = null,
        ?string $fields = null
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idChecklist;
        $query = '{+endpoint}/{id}?';

        if (! empty($cards)) {
            $params['cards'] = $cards;
            $query .= 'cards={cards}&';
        }

        if (! empty($checkItems)) {
            $params['checkItems'] = $checkItems;
            $query .= 'checkItems={checkItems}&';
        }

        if (! empty($checkItemsFields)) {
            $params['checkItemsFields'] = $checkItemsFields;
            $query .= 'checkItemsFields={checkItemsFields}&';
        }

        if (! empty($fields)) {
            $params['fields'] = $fields;
            $query .= 'fields={fields}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query.self::API_TOKEN_QUERY);
    }

    public function deleteChecklist(string $idChecklist): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idChecklist;
        $query = '{+endpoint}/{id}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query.self::API_TOKEN_QUERY);
    }

    public function getCheckItem(string $idChecklist, string $idCheckItem, ?string $fields = null): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idChecklist;
        $params['idCheckItem'] = $idCheckItem;
        $query = '{+endpoint}/{id}/checkItems/{idCheckItem}?';

        if (! empty($fields)) {
            $params['fields'] = $fields;
            $query .= 'fields={fields}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query.self::API_TOKEN_QUERY);
    }

    public function deteleteCheckItem(string $idChecklist, string $idCheckItem): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idChecklist;
        $params['idCheckItem'] = $idCheckItem;
        $query = '{+endpoint}/{id}/checkItems/{idCheckItem}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query.self::API_TOKEN_QUERY);
    }

    public function updateChecklist(string $idCheklist, ?string $name = null, ?string $pos = null): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['idCard'] = $idCheklist;
        $query = '{+endpoint}/{id}?';

        if (! empty($name)) {
            $params['name'] = $name;
            $query .= 'name={name}&';
        }

        if (! empty($pos)) {
            $params['pos'] = $pos;
            $query .= 'pos={post}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query.self::API_TOKEN_QUERY);
    }

    private function prepareApiTokenParams(): array
    {
        return ['key' => $this->apiKey, 'token' => $this->apiToken, 'endpoint' => self::CARDS_URI];
    }
}
