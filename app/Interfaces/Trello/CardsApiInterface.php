<?php

namespace App\Interfaces\Trello;

use Illuminate\Http\Client\Response;

interface CardsApiInterface
{
    // idMembers and idLabels shoud be commad separated list of member ids
    public function createNewCard(
        string $idList,
        ?string $name,
        ?string $desc,
        ?string $position,
        ?string $dueDate,
        ?string $startDate,
        ?bool $dueCompleteDate,
        ?string $idMembers,
        ?string $idLabels,
        ?string $urlSource,
        ?string $idCardSource
    ): Response;

    public function getCard(string $idCard): Response;

    public function updateCard(
        string $idCard,
        ?string $name,
        ?string $desc,
        ?bool $closed,
        ?string $idMembers,
        ?string $idAttachmentCover,
        ?string $idList,
        ?string $idLabels,
        ?string $idBoard,
        ?string $position,
        ?string $dueDate,
        ?string $startDate,
        ?bool $dueCompleteDate,
        ?bool $subscribed
    ): Response;

    public function deleteCard(string $idCard): Response;

    public function getBoardOfCard(string $idCard, string $fields): Response;

    public function addNewCommentToCard(string $idCard, string $text): Response;

    public function addLabelToCard(string $idCard, string $idLabel): Response;

    public function addMemberToCard(string $idCard, string $idMember): Response;

    public function createNewLabel(string $idCard, string $color, ?string $name): Response;

    public function markCardNotificationAsRead(string $idCard): Response;

    public function removeLabelFromCard(string $idCard, string $idLabel): Response;

    public function removeMemberFromCard(string $idCard, string $idMember): Response;
}
