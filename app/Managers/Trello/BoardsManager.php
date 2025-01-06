<?php

namespace App\Managers\Trello;

use App\Clients\TrelloClient;
use App\Interfaces\Trello\BoardsApiInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BoardsManager extends TrelloClient implements BoardsApiInterface
{
    private const BOARDS_URI = 'https://api.trello.com/1/boards';

    private const DEFAULT_FIELDS_VALUE = 'name,desc,descData,closed,idOrganization,pinned,shortUrl,prefs,labelNames';

    public function __construct()
    {
        parent::__construct();
    }

    public function getMemberships(
        string $boardId,
        string $filter = 'all',
        bool $activity = false,
        bool $orgMemberType = false
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;
        $params['filter'] = $filter;
        $params['activity'] = $activity;
        $params['orgMemberType'] = $orgMemberType;

        $query = '{+endpoint}/{id}/memberships?{filter}&activity={activity}&orgMemberType={orgMemberType}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query . self::API_TOKEN_QUERY);
    }

    public function getBoard(
        string $boardId,
        string $fields = self::DEFAULT_FIELDS_VALUE,
        bool $myPrefs = false,
        bool $tags = false
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;
        $params['fields'] = $fields;
        $params['myPrefs'] = $myPrefs;
        $params['tags'] = $tags;

        $query = '{+endpoint}/{id}?fields={fields}&myPrefs={myPrefs}&tags={tags}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query . self::API_TOKEN_QUERY);
    }

    // TODO implement query params: permission level, invitations, comments, calendar feed enabled, label names
    // reimplement function
    public function updateBoard(
        string $boardId,
        ?string $name,
        ?string $desc,
        ?bool $closed,
        ?string $subscribed,
        ?string $idOrganization,
    ): Response {
        $params = $this->prepareApiTokenParams();

        $params['id'] = $boardId;
        $query = '{+endpoint}/{id}?';

        if (!empty($name)) {
            $params['name'] = $name;
            $query .= 'name={name}&';
        }

        if (!empty($desc)) {
            $params['desc'] = $desc;
            $query .= 'desc={desc}&';
        }

        if (!empty($closed)) {
            $params['closed'] = $closed;
            $query .= 'closed={closed}&';
        }

        if (!empty($closed)) {
            $params['subscribed'] = $subscribed;
            $query .= 'subscribed={subscribed}&';
        }

        if (!empty($closed)) {
            $params['idOrganization'] = $idOrganization;
            $query .= 'idOrganization={idOrganization}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query  . self::API_TOKEN_QUERY);
    }

    public function deleteBoard(string $boardId): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;

        $query = '{+endpoint}/{id}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query . self::API_TOKEN_QUERY);
    }

    public function getMembers(string $boardId): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;

        $query = '{+endpoint}/{id}/members?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query . self::API_TOKEN_QUERY);
    }

    public function inviteMemberViaEmail(string $boardId, string $email, string $type): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;
        $params['email'] = $email;
        $params['type'] = $type;

        $query = '{+endpoint}/{id}/members?email={email}&type={type}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query . self::API_TOKEN_QUERY);
    }

    public function addMemberToBoard(string $boardId, string $idMember, string $type): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;
        $params['idMember'] = $idMember;
        $params['type'] = $type;

        $query = '{+endpoint}/{id}/members/{idMember}?type={type}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query . self::API_TOKEN_QUERY);
    }

    public function removeMember(string $boardId, string $idMember): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;
        $params['idMember'] = $idMember;

        $query = '{+endpoint}/{id}/members/{idMember}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query . self::API_TOKEN_QUERY);
    }

    public function createBoard(
        string $name,
        string $desc,
        string $idOrganization,
        ?string $idBoardSource = null,
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['name'] = $name;
        $params['desc'] = $desc;
        $params['idOrganization'] = $idOrganization;

        $query = '{+endpoint}/?name={name}&desc={desc}&idOrganization={idOrganization}&';

        if (!empty($idBoardSource)) {
            $params['idBoardSource'] = $idBoardSource;
            $query .= 'idBoardSource={idBoardSource}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query . self::API_TOKEN_QUERY);
    }

    private function prepareApiTokenParams(): array
    {
        return ['key' => $this->apiKey, 'token' => $this->apiToken, 'endpoint' => self::BOARDS_URI];
    }

    public function getCards(string $boardId): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;

        $query = '{+endpoint}/{id}/cards?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query . self::API_TOKEN_QUERY);
    }

    public function getFilteredCards(string $boardId, string $filter): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;
        $params['filter'] = $filter;

        $query = '{+endpoint}/{id}/cards?{filter}}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query . self::API_TOKEN_QUERY);
    }

    public function getLabels(string $boardId): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;

        $query = '{+endpoint}/{id}/labels?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query . self::API_TOKEN_QUERY);
    }

    public function createLabel(string $boardId, string $name, string $color): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;
        $params['name'] = $name;
        $params['color'] = $color;

        $query = '{+endpoint}/{id}/labels?name={name}&color={color}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query . self::API_TOKEN_QUERY);
    }

    public function getLists(string $boardId): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;

        $query = '{+endpoint}/{id}/lists?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query . self::API_TOKEN_QUERY);
    }
}
