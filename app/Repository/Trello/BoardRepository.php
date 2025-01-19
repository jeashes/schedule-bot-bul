<?php

namespace App\Repository\Trello;

use App\Dto\Trello\BoardDto;
use App\Models\Mongo\TrelloBoard;
use App\Models\Mongo\User;
use App\Models\Mongo\Workspace;
use App\Service\Trello\Boards\BoardClient;
use Throwable;

class BoardRepository
{
    public function __construct(private readonly BoardClient $client)
    {

    }
    public function createAndStoreBoard(Workspace $workspace, User $user): TrelloBoard
    {
        $response = $this->client->createBoard(
            name: $workspace->getName(),
            desc: __('trello.workspace_description'),
            idOrganization: config('trello.organization_id')
        );

        $data = json_decode($response, true);

        $dto = new BoardDto($data);

        return $this->saveBoard($user->getId(), $dto);
    }

    public function saveBoard(string $userId, BoardDto $dto): TrelloBoard
    {
        return TrelloBoard::firstOrCreate(
            [
                'trello_id' => $dto->id,
                'user_id' => $userId,
            ],
            [
                'name' => $dto->name,
                'desc' => $dto->desc,
                'closed' => $dto->closed,
                'url' => $dto->url,
                'permission_level' => $dto->permissionLevel,
            ]
        );
    }

    public function userBoardWasCreated(string $userId): bool
    {
        try {
            TrelloBoard::query()->where(['user_id' => $userId])->firstOrFail();
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function getBoardByUserId(string $userId): TrelloBoard
    {
        return TrelloBoard::query()->where(['user_id' => $userId])->firstOrFail();
    }
}
