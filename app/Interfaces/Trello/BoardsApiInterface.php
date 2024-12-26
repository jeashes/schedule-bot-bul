<?php

namespace App\Interfaces\Trello;

use Illuminate\Http\Response;

interface BoardsApiInterface
{
    public function getMemberships(string $boardId): Response;

    public function getBoard(string $boardId): Response;

    public function updateBoard(string $boardId): Response;

    public function deleteBoard(string $boardId): Response;

    public function getFieldBoard(string $boardId, string $field): Response;

    public function getActions(string $boardId): Response;

    public function getChecklists(string $boardId): Response;

    public function getCards(string $boardId): Response;

    public function getFilteredCards(string $boardId, string $filter): Response;

    public function getLabels(string $boardId, ?string $fields, ?int $limit): Response;

    public function getLists(string $boardId, ?string $cards, ?string $cardFields, ?string $filter, ?string $fields): Response;

    public function createList(string $boardId, string $name, ?string $post): Response;

    public function getFilteredLists(string $boardId, string $filter): Response;

    public function getMembers(string $boardId): Response;

    public function inviteMemberViaEmail(string $boardId, string $email, ?string $type): Response;

    public function addMemberToBoard(string $boardId, string $idMember, string $type, ?string $allowBillableGuest): Response;

    public function removeMember(string $boardId, string $idMember): Response;

    public function updateMembershipForMember(string $boardId, string $idMembership, string $type, string $memberFields): Response;

    public function createBoard(
        string $name,
        ?string $defaultLabels,
        ?string $defaultLists,
        ?string $desc,
        ?string $idOrganization,
        ?string $idBoardSource,
        ?string $keepFromSource,
        ?string $powerUps,
        ?string $prefsPermissionLevel,
        ?string $prefsVoting,
        ?string $prefsComments,
        ?string $prefsInvitations,
        ?string $prefsSelfJoin,
        ?string $prefsCardCovers,
        ?string $prefsBackground,
        ?string $prefsCardAging
    ): Response;

    public function markBoardAsViewed(string $boardI): Response;
}
