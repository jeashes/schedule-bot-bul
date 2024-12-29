<?php

namespace App\Interfaces\Trello;

use Illuminate\Http\Client\Response;

interface BoardsApiInterface
{
    public function getMemberships(string $boardId, string $filter, bool $activity, bool $orgMemberType): Response;

    public function getBoard(string $boardId, string $fields, bool $myPrefs, bool $tags): Response;

    public function updateBoard(string $boardId, string $name, string $desc, bool $closed, string $subscribed, string $idOrganization): Response;

    public function deleteBoard(string $boardId): Response;

    public function getMembers(string $boardId): Response;

    public function inviteMemberViaEmail(string $boardId, string $email, string $type): Response;

    public function addMemberToBoard(string $boardId, string $idMember, string $type): Response;

    public function removeMember(string $boardId, string $idMember): Response;

    public function createBoard(string $name, string $desc, string $idOrganization, ?string $idBoardSource): Response;
}
