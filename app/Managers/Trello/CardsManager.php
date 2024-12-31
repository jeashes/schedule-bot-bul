<?php

namespace App\Managers\Trello;

use App\Clients\TrelloClient;
use App\Interfaces\Trello\CardsApiInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CardsManager extends TrelloClient implements CardsApiInterface
{
    private const CARDS_URI = 'https://api.trello.com/1/cards';

    public function __construct()
    {
        self::__construct();
    }

    public function getCard(string $idCard): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;

        $query = '{+endpoint}/{id}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query . self::API_TOKEN_QUERY);
    }

    public function updateCard(
        string $idCard, ?string $name, ?string $desc, ?bool $closed, ?string $idMembers,
        ?string $idAttachmentCover, ?string $idList, ?string $idLabels, ?string $idBoard,
        ?string $position, ?string $dueDate, ?string $startDate, ?bool $dueCompleteDate,
        ?bool $subscribed
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;

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
            $params['closed'] = $desc;
            $query .= 'closed={closed}&';
        }

        if (!empty($idMembers)) {
            $params['idMembers'] = $idMembers;
            $query .= 'idMembers={idMembers}&';
        }

        if (!empty($idAttachmentCover)) {
            $params['idMembers'] = $idMembers;
            $query .= 'idMembers={idMembers}&';
        }

        if (!empty($idList)) {
            $params['idList'] = $idList;
            $query .= 'idList={idList}&';
        }

        if (!empty($idLabels)) {
            $params['idLabels'] = $idLabels;
            $query .= 'idLabels={idLabels}&';
        }

        if (!empty($idBoard)) {
            $params['idBoard'] = $idBoard;
            $query .= 'idBoard={idBoard}&';
        }

        if (!empty($position)) {
            $params['position'] = $position;
            $query .= 'pos={position}&';
        }

        if (!empty($dueDate)) {
            $params['due'] = $dueDate;
            $query .= 'due={due}&';
        }

        if (!empty($startDate)) {
            $params['start'] = $dueDate;
            $query .= 'start={start}&';
        }

        if (!empty($dueCompleteDate)) {
            $params['dueComplete'] = $dueCompleteDate;
            $query .= 'dueComplete={dueComplete}&';
        }

        if (!empty($subscribed)) {
            $params['subscribed'] = $subscribed;
            $query .= 'subscribed={subscribed}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query . self::API_TOKEN_QUERY);
    }

    public function deleteCard(string $idCard): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;

        $query = '{+endpoint}/{id}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query . self::API_TOKEN_QUERY);
    }

    public function createNewCard(
        string $idList, ?string $name, ?string $desc, ?string $position, ?string $dueDate,
        ?string $startDate, ?bool $dueCompleteDate, ?string $idMembers, ?string $idLabels,
        ?string $urlSource, ?string $idCardSource
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['idList'] = $idList;
        $query = '{+endpoint}?idList={idList}&';

        if (!empty($name)) {
            $params['name'] = $name;
            $query .= 'name={name}&';
        }

        if (!empty($desc)) {
            $params['desc'] = $desc;
            $query .= 'desc={desc}&';
        }

        if (!empty($position)) {
            $params['position'] = $position;
            $query .= 'pos={position}&';
        }

        if (!empty($dueDate)) {
            $params['dueDate'] = $dueDate;
            $query .= 'due={due}&';
        }

        if (!empty($startDate)) {
            $params['start'] = $startDate;
            $query .= 'start={start}&';
        }

        if (!empty($dueCompleteDate)) {
            $params['dueComplete'] = $dueCompleteDate;
            $query .= 'dueComplete={dueComplete}&';
        }

        if (!empty($idMembers)) {
            $params['idMembers'] = $idMembers;
            $query .= 'idMembers={idMembers}';
        }

        if (!empty($idLabels)) {
            $params['idLabels'] = $idMembers;
            $query .= 'idLabels={idLabels}';
        }

        if (!empty($urlSource)) {
            $params['urlSource'] = $urlSource;
            $query .= 'urlSource={urlSource}';
        }

        if (!empty($idCardSource)) {
            $params['idCardSource'] = $idCardSource;
            $query .= 'idCardSource={idCardSources}';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query . self::API_TOKEN_QUERY);
    }

    public function getBoardOfCard(string $idCard, string $fields): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['fields'] = $fields;

        $query = '{+endpoint}/{id}?';

        if (!empty($fields)) {
            $params['fields'] = $fields;
            $query .= 'fields={fields}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query . self::API_TOKEN_QUERY);
    }

    public function addNewCommentToCard(string $idCard, string $text): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['text'] = $text;

        $query = '{+endpoint}/{id}/actions/comments?text={text}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query . self::API_TOKEN_QUERY);
    }

    public function addLabelToCard(string $idCard, string $idLabel): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['value'] = $idLabel;

        $query = '{+endpoint}/{id}/idLabels?value={value}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query . self::API_TOKEN_QUERY);
    }

    public function addMemberToCard(string $idCard, string $idMember): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['value'] = $idMember;

        $query = '{+endpoint}/{id}/idMembers?value={value}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query . self::API_TOKEN_QUERY);
    }

    public function createNewLabel(string $idCard, string $color, ?string $name): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['color'] = $color;

        $query = '{+endpoint}/{id}/labels?color={color}?';

        if (!empty($name)) {
            $params['name'] = $name;
            $query .= 'name={name}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query . self::API_TOKEN_QUERY);
    }

    public function markCardNotificationAsRead(string $idCard): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;

        $query = '{+endpoint}/{id}/markAssociatedNotificationsRead?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query . self::API_TOKEN_QUERY);
    }

    public function removeLabelFromCard(string $idCard, string $idLabel): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['idLabel'] = $idLabel;

        $query = '{+endpoint}/{id}/idLabels/{idLabel}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query . self::API_TOKEN_QUERY);
    }

    public function removeMemberFromCard(string $idCard, string $idMember): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['idMember'] = $idMember;

        $query = '{+endpoint}/{id}/idMembers/{idMember}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query . self::API_TOKEN_QUERY);
    }

    private function prepareApiTokenParams(): array
    {
        return ['key' => $this->apiKey, 'token' => $this->apiToken, 'endpoint' => self::CARDS_URI];
    }
}
