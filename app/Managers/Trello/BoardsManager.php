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
        self::__construct();
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

        $query = '{+endpoint}/{id}/memberships?filter={filter}&activity={activity}&orgMemberType={orgMemberType}&';

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
    public function updateBoard(
        string $boardId,
        string $name,
        string $desc,
        bool $closed,
        string $subscribed,
        string $idOrganization,
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;
        $params['name'] = $name;
        $params['desc'] = $desc;
        $params['closed'] = $closed;
        $params['subscribed'] = $subscribed;
        $params['idOrganization'] = $idOrganization;

        $query = '{+endpoint}/{id}?name={name}&desc={desc}&closed={closed}&subscribed={subscribed}&idOrganization={idOrganization}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query  . self::API_TOKEN_QUERY);
    }

    public function deleteBoard(string $boardId): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;

        $query = '{+endpoint}/{id}/members?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query . self::API_TOKEN_QUERY);
    }

    public function getMembers(string $boardId): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $boardId;

        $query = '{+endpoint}/{id}?';

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
        ?string $idBoardSource
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['name'] = $name;
        $params['desc'] = $desc;
        $params['idOrganization'] = $idOrganization;

        $query = '{+endpoint}/?name={name}&desc={desc}&idOrganization={idOrganization}&';

        if (!empty($idBoardSource)) {
            $params['idBoardSource'] = $idBoardSource;
            $query .= '&idBoardSource={idBoardSource}';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query . self::API_TOKEN_QUERY);
    }


    private function prepareApiTokenParams(): array
    {
        return ['key' => $this->apiKey, 'token' => $this->apiToken, 'endpoint' => self::BOARDS_URI];
    }
}
