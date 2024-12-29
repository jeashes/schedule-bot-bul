<?php

namespace App\Interfaces\Trello;

use Illuminate\Http\Client\Response;

interface ListsApiInterface
{
    public function getList(string $idList): Response;

    public function updateList(
        string $idList,
        ?string $name,
        ?bool $closed,
        ?string $idBoard,
        ?string $position,
        ?bool $subscribed
    ): Response;

    public function createNewList(string $name, string $idBoard, ?string $idListSource, ?string $position): Response;

    public function archiveAllCards(string $idList): Response;

    public function moveAllCards(string $fromIdList, string $idBoard, string $toIdList): Response;

    public function archiveOrUnarchiveList(string $idList, bool $value): Response;

    public function moveListToBoard(string $idList, string $idBoard): Response;

    public function updateFieldAtList(string $idList, string $field): Response;

    public function getBoardOfList(string $idList): Response;

    public function getCardsOfList(string $idList): Response;
}
