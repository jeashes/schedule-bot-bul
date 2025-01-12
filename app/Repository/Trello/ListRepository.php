<?php

namespace App\Repository\Trello;

use App\Dto\Trello\ListDto;
use App\Enums\Trello\DefaultListNameEnum;
use App\Models\Mongo\TrelloList;
use App\Service\Trello\Lists\ListClient;

class ListRepository
{
    public function __construct(private readonly ListClient $client)
    {

    }

    public function saveDefaultLists(string $userId, array $data): void
    {
        foreach ($data as $list) {
            switch ($list['name']) {
                case DefaultListNameEnum::TODO->value:
                    $this->saveList($userId, new ListDto($list['name']));
                    break;
                case DefaultListNameEnum::DOING->value:
                    $this->saveList($userId, new ListDto($list['name']));
                    break;
                case DefaultListNameEnum::DONE->value:
                    $this->saveList($userId, new ListDto($list['name']));
                    break;
            }
        }
    }

    public function saveList(string $userId, ListDto $dto): TrelloList
    {
        return TrelloList::query()->firstOrCreate([
            'trello_id' => $dto->id,
            'user_id' => $userId,
            'board_id' => $dto->idBoard,
            'name' => $dto->name
        ]);
    }

    public function getToDoList(string $userId): TrelloList
    {
        return TrelloList::query()->where([
            'user_id' => $userId,
            'name' => DefaultListNameEnum::TODO->value
        ])->first();
    }
}
