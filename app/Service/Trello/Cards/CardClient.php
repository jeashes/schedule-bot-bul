<?php

namespace App\Service\Trello\Cards;

use App\Interfaces\Trello\CardsApiInterface;
use App\Service\Trello\BaseClient;
use App\Service\Trello\TrelloConfig;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CardClient extends BaseClient implements CardsApiInterface
{
    private const CARDS_URI = 'https://api.trello.com/1/cards';

    public function __construct(TrelloConfig $config)
    {
        parent::__construct($config->apiKey, $config->apiToken);
    }

    public function getCard(string $idCard): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;

        $query = '{+endpoint}/{id}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->get($query.self::API_TOKEN_QUERY);
    }

    public function updateCard(
        string $idCard, ?string $name = null, ?string $desc = null, ?bool $closed = null, ?string $idMembers = null,
        ?string $idAttachmentCover = null, ?string $idList = null, ?string $idLabels = null, ?string $idBoard = null,
        ?string $position = null, ?string $dueDate = null, ?string $startDate = null, ?bool $dueCompleteDate = null,
        ?bool $subscribed = null
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;

        $query = '{+endpoint}/{id}?';

        if (! empty($name)) {
            $params['name'] = $name;
            $query .= 'name={name}&';
        }

        if (! empty($desc)) {
            $params['desc'] = $desc;
            $query .= 'desc={desc}&';
        }

        if (! empty($closed)) {
            $params['closed'] = $desc;
            $query .= 'closed={closed}&';
        }

        if (! empty($idMembers)) {
            $params['idMembers'] = $idMembers;
            $query .= 'idMembers={idMembers}&';
        }

        if (! empty($idAttachmentCover)) {
            $params['idMembers'] = $idMembers;
            $query .= 'idMembers={idMembers}&';
        }

        if (! empty($idList)) {
            $params['idList'] = $idList;
            $query .= 'idList={idList}&';
        }

        if (! empty($idLabels)) {
            $params['idLabels'] = $idLabels;
            $query .= 'idLabels={idLabels}&';
        }

        if (! empty($idBoard)) {
            $params['idBoard'] = $idBoard;
            $query .= 'idBoard={idBoard}&';
        }

        if (! empty($position)) {
            $params['position'] = $position;
            $query .= 'pos={position}&';
        }

        if (! empty($dueDate)) {
            $params['due'] = $dueDate;
            $query .= 'due={due}&';
        }

        if (! empty($startDate)) {
            $params['start'] = $dueDate;
            $query .= 'start={start}&';
        }

        if (! empty($dueCompleteDate)) {
            $params['dueComplete'] = $dueCompleteDate;
            $query .= 'dueComplete={dueComplete}&';
        }

        if (! empty($subscribed)) {
            $params['subscribed'] = $subscribed;
            $query .= 'subscribed={subscribed}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query.self::API_TOKEN_QUERY);
    }

    public function deleteCard(string $idCard): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;

        $query = '{+endpoint}/{id}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query.self::API_TOKEN_QUERY);
    }

    public function createCheckList(string $idCard, string $name, ?string $idCheckListResource = null, ?string $pos = null): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['name'] = $name;

        $query = '{+endpoint}/{id}/checklists?name={name}&';

        if (! empty($idCheckListResource)) {
            $params['idCheckListResource'] = $idCheckListResource;
            $query .= 'idCheckListResource={idCheckListResource}&';
        }

        if (! empty($pos)) {
            $params['pos'] = $pos;
            $query .= 'pos={pos}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);

    }

    public function createCheckItem(string $idCard, string $idCheckList, string $name): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['idCheckList'] = $idCheckList;
        $params['name'] = $name;

        $query = '{+endpoint}/{id}/checklists/{idCheckList}/checkItems?name={name}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function createNewCard(
        string $idList, ?string $name = null, ?string $desc = null, ?string $position = null, ?string $dueDate = null,
        ?string $startDate = null, ?bool $dueCompleteDate = null, ?string $idMembers = null, ?string $idLabels = null,
        ?string $urlSource = null, ?string $idCardSource = null
    ): Response {
        $params = $this->prepareApiTokenParams();
        $params['idList'] = $idList;
        $query = '{+endpoint}?idList={idList}&';

        if (! empty($name)) {
            $params['name'] = $name;
            $query .= 'name={name}&';
        }

        if (! empty($desc)) {
            $params['desc'] = $desc;
            $query .= 'desc={desc}&';
        }

        if (! empty($position)) {
            $params['position'] = $position;
            $query .= 'pos={position}&';
        }

        if (! empty($dueDate)) {
            $params['due'] = $dueDate;
            $query .= 'due={due}&';
        }

        if (! empty($startDate)) {
            $params['start'] = $startDate;
            $query .= 'start={start}&';
        }

        if (! empty($dueCompleteDate)) {
            $params['dueComplete'] = $dueCompleteDate;
            $query .= 'dueComplete={dueComplete}&';
        }

        if (! empty($idMembers)) {
            $params['idMembers'] = $idMembers;
            $query .= 'idMembers={idMembers}';
        }

        if (! empty($idLabels)) {
            $params['idLabels'] = $idMembers;
            $query .= 'idLabels={idLabels}';
        }

        if (! empty($urlSource)) {
            $params['urlSource'] = $urlSource;
            $query .= 'urlSource={urlSource}';
        }

        if (! empty($idCardSource)) {
            $params['idCardSource'] = $idCardSource;
            $query .= 'idCardSource={idCardSources}';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function getBoardOfCard(string $idCard, string $fields): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['fields'] = $fields;

        $query = '{+endpoint}/{id}?';

        if (! empty($fields)) {
            $params['fields'] = $fields;
            $query .= 'fields={fields}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->put($query.self::API_TOKEN_QUERY);
    }

    public function addNewCommentToCard(string $idCard, string $text): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['text'] = $text;

        $query = '{+endpoint}/{id}/actions/comments?text={text}&';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function addLabelToCard(string $idCard, string $idLabel): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['value'] = $idLabel;

        $query = '{+endpoint}/{id}/idLabels?value={value}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function addMemberToCard(string $idCard, string $idMember): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['value'] = $idMember;

        $query = '{+endpoint}/{id}/idMembers?value={value}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function createNewLabel(string $idCard, string $color, ?string $name): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['color'] = $color;

        $query = '{+endpoint}/{id}/labels?color={color}?';

        if (! empty($name)) {
            $params['name'] = $name;
            $query .= 'name={name}&';
        }

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function markCardNotificationAsRead(string $idCard): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;

        $query = '{+endpoint}/{id}/markAssociatedNotificationsRead?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->post($query.self::API_TOKEN_QUERY);
    }

    public function removeLabelFromCard(string $idCard, string $idLabel): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['idLabel'] = $idLabel;

        $query = '{+endpoint}/{id}/idLabels/{idLabel}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query.self::API_TOKEN_QUERY);
    }

    public function removeMemberFromCard(string $idCard, string $idMember): Response
    {
        $params = $this->prepareApiTokenParams();
        $params['id'] = $idCard;
        $params['idMember'] = $idMember;

        $query = '{+endpoint}/{id}/idMembers/{idMember}?';

        return Http::withUrlParameters($params)
            ->withHeaders($this->prepareHeaders())
            ->delete($query.self::API_TOKEN_QUERY);
    }

    private function prepareApiTokenParams(): array
    {
        return ['key' => $this->apiKey, 'token' => $this->apiToken, 'endpoint' => self::CARDS_URI];
    }
}
