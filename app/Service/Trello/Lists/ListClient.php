<?php

namespace App\Service\Trello\Lists;

use App\Service\Trello\BaseClient;
use App\Interfaces\Trello\ListsApiInterface;
use Illuminate\Http\Client\Response;
use App\Service\Trello\TrelloConfig;
use Illuminate\Support\Facades\Http;

class ListClient extends BaseClient implements ListsApiInterface
{
    private const LISTS_URI = 'https://api.trello.com/1/lists';

    public function __construct(TrelloConfig $config)
    {
        parent::__construct($config->apiKey, $config->apiToken);
    }

    public function createNewList(
        string $name, string $idBoard,
        ?string $idListSource = null, ?string $position = null
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['name'] = $name;
        $params['idBoard'] = $idBoard;

        $query = '{+endpoint}?name={name}&idBoard={idBoard}&';

        if (!empty($idListSource)) {
            $params['idListSource'] = $idListSource;
            $query .= 'idListSource={idListSource}&';
        }

        if (!empty($position)) {
            $params['pos'] = $position;
            $query .= 'pos={pos}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query  . self::API_TOKEN_QUERY);
    }

    public function getList(string $idList): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idList;

        $query = '{+endpoint}/{id}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query  . self::API_TOKEN_QUERY);
    }

    public function updateList(
        string $idList, ?string $name = null, ?bool $closed = null, ?string $idBoard = null,
        ?string $position = null, ?bool $subscribed = null
    ): Response {
        $params = $this->prepareApiTokenParams();

        $params['id'] = $idList;

        $query = '{+endpoint}/{id}?';

        if (!empty($name)) {
            $params['name'] = $name;
            $query .= 'name={name}&';
        }

        if (!empty($closed)) {
            $params['closed'] = $closed;
            $query .= 'closed={closed}&';
        }

        if (!empty($idBoard)) {
            $params['idBoard'] = $idBoard;
            $query .= 'idBoard={idBoard}&';
        }

        if (!empty($position)) {
            $params['pos'] = $position;
            $query .= 'pos={pos}&';
        }

        if (!empty($subscribed)) {
            $params['subscribed'] = $subscribed;
            $query .= 'subscribed={subscribed}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query  . self::API_TOKEN_QUERY);
    }

    public function archiveAllCards(string $idList): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idList;

        $query = '{+endpoint}/{id}/archiveAllCards?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query  . self::API_TOKEN_QUERY);
    }

    public function moveAllCards(string $idList, string $idBoard, string $toIdList): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idList;
        $params['idBoard'] = $idBoard;
        $params['idList'] = $toIdList;

        $query = '{+endpoint}/{id}/moveAllCards?idBoard={idBoard}&idList={idList}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query  . self::API_TOKEN_QUERY);
    }

    public function archiveOrUnarchiveList(string $idList, bool $value): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idList;
        $params['value'] = $value;
        $query = '{+endpoint}/{id}/closed?value={value}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query  . self::API_TOKEN_QUERY);
    }

    public function moveListToBoard(string $idList, string $idBoard): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idList;
        $params['idBoard'] = $idBoard;

        $query = '{+endpoint}/{id}/idBoard={idBoard}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query  . self::API_TOKEN_QUERY);
    }

    public function updateFieldAtList(string $idList, string $field): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idList;
        $params['field'] = $field;

        $query = '{+endpoint}/{id}/{field}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query  . self::API_TOKEN_QUERY);
    }

    public function getBoardOfList(string $idList): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idList;

        $query = '{+endpoint}/{id}/board?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query  . self::API_TOKEN_QUERY);
    }

    public function getCardsInList(string $idList): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idList;

        $query = '{+endpoint}/{id}/cards?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query  . self::API_TOKEN_QUERY);
    }

    private function prepareApiTokenParams(): array
    {
        return ['key' => $this->apiKey, 'token' => $this->apiToken, 'endpoint' => self::LISTS_URI];
    }
}
