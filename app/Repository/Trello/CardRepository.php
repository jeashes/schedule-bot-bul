<?php

namespace App\Repository\Trello;

use App\Dto\Trello\CardDto;
use App\Models\Mongo\TrelloCard;

class CardRepository
{
    public function saveCard(string $userId, CardDto $dto): TrelloCard
    {
        return TrelloCard::firstOrCreate(
            [
                'user_id' => $userId,
                'trello_id' => $dto->id,
                'board_id' => $dto->idBoard,
                'url' => $dto->url,
                'due' => $dto->closed,
                'dueComplete' => $dto->dueComplete,
                'email' => $dto->email
            ],
            [
                'name' => $dto->name,
                'desc' => $dto->desc,
            ]
        );
    }
}
