<?php

namespace App\Interfaces\Trello;

use Illuminate\Http\Client\Response;

interface MembersApiInterface
{
    public function getMember(
        string $idMember, ?string $actions, ?string $boards, ?string $boardBackgrounds,
        ?string $boardsInvited, ?string $boardsInvitedFields, ?string $cards,
        ?string $customBoardBackgrounds
    ): Response;

    public function updateMember(string $idMember, ?string $fullName, ?string $initials, ?string $username, ?string $bio): Response;

    public function uploadMemberNewBoardBackground(string $idMember, string $file): Response;

    public function getBoardBackground(string $idMember, string $idBackground, ?string $fields): Response;

    public function updateMemberBoardBackground(string $idMember, string $idBackground, ?string $brightness, ?string $title): Response;

    public function deleteMemberBoardBackground(string $idMember, string $idBackground): Response;

    public function createNewCustomBoardBackground(string $idMember, string $file): Response;

    public function updateCustomBoardBackground(string $idMember, string $idBackground, ?string $brightness, ?string $title): Response;

    public function deleteCustomBoardBackground(string $idMember, string $idBackground): Response;
}
