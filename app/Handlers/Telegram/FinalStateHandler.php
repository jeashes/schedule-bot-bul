<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Dto\UserWorkspaceDto;
use App\Enums\Telegram\UserEmailEnum;
use App\Jobs\CreateUserTrelloWorkspace;
use App\Repository\TrelloWorkSpaceRepository;
use App\Repository\UserRepository;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;

class FinalStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly TrelloWorkSpaceRepository $trelloWorkSpaceRepository,
        private readonly UserRepository $userRepository,
    ) { }

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        if ($chatState === ChatStateEnum::FINISHED->value) {
            $dto = $this->prepareUserWorkspaceForCreating($messageDto->user->getId());
            dispatch(new CreateUserTrelloWorkspace($dto->workspace, $dto->user));

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __(
                    'bot_messages.trello_workspace_created', [
                        'name' => $messageDto->user->getFirstName().' '
                        .$messageDto->user->getLastName(),
                    ]
                ),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function prepareUserWorkspaceForCreating(string $userId): UserWorkspaceDto
    {
        $userEmailInfo = json_decode(
            Redis::get($userId.'_'.UserEmailEnum::QUESTION->value), true);

        $workspaceParams = $this->trelloWorkSpaceRepository->getWorkspaceParamsFromRedis($userId);
        $workspace = $this->trelloWorkSpaceRepository->createWorkspaceByUserId(
            $workspaceParams,
            $userId
        );

        $user = $this->userRepository->findById($userId);
        $user->update(['email' => $userEmailInfo['current_answer']]);

        return new UserWorkspaceDto($workspace, $user);
    }
}