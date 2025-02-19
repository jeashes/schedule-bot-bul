<?php

namespace App\Service\Trello\Members;

use App\Interfaces\Trello\MembersApiInterface;
use App\Service\Trello\BaseClient;
use App\Service\Trello\TrelloConfig;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class MemberClient extends BaseClient implements MembersApiInterface
{
    private const LISTS_URI = 'https://api.trello.com/1/members';

    public function __construct(TrelloConfig $config)
    {
        parent::__construct($config->apiKey, $config->apiToken);
    }

    public function getMember(
        string $idMember, ?string $actions = null, ?string $boards = null, ?string $boardBackgrounds = null,
        ?string $boardsInvited = null, ?string $boardsInvitedFields = null, ?string $cards = null,
        ?string $customBoardBackgrounds = null
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idMember;
        $query = '{+endpoint}/{id}&';

        if (! empty($actions)) {
            $params['actions'] = $actions;
            $query .= 'actions={actions}&';
        }

        if (! empty($boards)) {
            $params['boards'] = $boards;
            $query .= 'boards={boards}&';
        }

        if (! empty($boardBackgrounds)) {
            $params['boardBackgrounds'] = $boards;
            $query .= 'boardBackgrounds={boardBackgrounds}&';
        }

        if (! empty($boardsInvited)) {
            $params['boardsInvited'] = $boardsInvited;
            $query .= 'boardsInvited={boardsInvited}&';
        }

        if (! empty($boardsInvitedFields)) {
            $params['boardsInvitedFields'] = $boardsInvitedFields;
            $query .= 'boardsInvited_fields={boardsInvitedFields}&';
        }

        if (! empty($cards)) {
            $params['cards'] = $cards;
            $query .= 'cards={cards}&';
        }

        if (! empty($customBoardBackgrounds)) {
            $params['customBoardBackgrounds'] = $customBoardBackgrounds;
            $query .= 'customBoardBackgrounds={customBoardBackgrounds}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query.self::API_TOKEN_QUERY);
    }

    public function updateMember(
        string $idMember, ?string $fullName = null,
        ?string $initials = null, ?string $username = null,
        ?string $bio = null
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idMember;
        $query = '{+endpoint}/{id}&';

        if (! empty($fullName)) {
            $params['fullName'] = $fullName;
            $query .= 'fullName={fullName}&';
        }

        if (! empty($initials)) {
            $params['initials'] = $fullName;
            $query .= 'initials={initials}&';
        }

        if (! empty($username)) {
            $params['username'] = $username;
            $query .= 'username={username}&';
        }

        if (! empty($bio)) {
            $params['bio'] = $username;
            $query .= 'bio={bio}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query.self::API_TOKEN_QUERY);
    }

    public function uploadMemberNewBoardBackground(string $idMember, string $file): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idMember;
        $params['file'] = $file;

        $query = '{+endpoint}/{id}/boardBackgrounds?';

        return Http::withUrlParameters($params)
            ->asMultipart()
            ->attach('file', file_get_contents(public_path('images/'.$file)), basename($file))
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function getBoardBackground(string $idMember, string $idBackground, ?string $fields = null): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idMember;
        $params['idBackground'] = $idBackground;
        $query = '{+endpoint}/{id}/boardBackgrounds/{idBackground}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query.self::API_TOKEN_QUERY);
    }

    public function updateMemberBoardBackground(string $idMember, string $idBackground, ?string $brightness = null, ?string $title = null): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idMember;
        $params['idBackground'] = $idBackground;
        $query = '{+endpoint}/{id}/boardBackgrounds/{idBackground}?';

        if (! empty($brightness)) {
            $params['brightness'] = $brightness;
            $query .= 'brightness={brightness}&';
        }

        if (! empty($title)) {
            $params['title'] = $title;
            $query .= 'title={title}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query.self::API_TOKEN_QUERY);
    }

    public function deleteMemberBoardBackground(string $idMember, string $idBackground): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idMember;
        $params['idBackground'] = $idBackground;
        $query = '{+endpoint}/{id}/boardBackgrounds/{idBackground}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query.self::API_TOKEN_QUERY);
    }

    public function createNewCustomBoardBackground(string $idMember, string $file): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idMember;
        $params['file'] = $file;
        $query = '{+endpoint}/{id}/customBoardBackgrounds?file={file}&';

        return Http::withUrlParameters($params)
            ->asMultipart()
            ->attach('file', file_get_contents(public_path('images/'.$file)), basename($file))
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function updateCustomBoardBackground(string $idMember, string $idBackground, ?string $brightness = null, ?string $title = null): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idMember;
        $params['idBackground'] = $idBackground;
        $query = '{+endpoint}/{id}/customBoardBackgrounds/{idBackground}?';

        if (! empty($brightness)) {
            $params['brightness'] = $brightness;
            $query .= 'brightness={brightness}&';
        }

        if (! empty($title)) {
            $params['title'] = $title;
            $query .= 'title={title}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query.self::API_TOKEN_QUERY);
    }

    public function deleteCustomBoardBackground(string $idMember, string $idBackground): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idMember;
        $params['idBackground'] = $idBackground;
        $query = '{+endpoint}/{id}/customBoardBackgrounds/{idBackground}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query.self::API_TOKEN_QUERY);
    }

    private function prepareApiTokenParams(): array
    {
        return ['key' => $this->apiKey, 'token' => $this->apiToken, 'endpoint' => self::LISTS_URI];
    }
}
