<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Dto\UserWorkspaceDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\UserEmailEnum;
use App\Jobs\CreateUserTrelloWorkspace;
use Throwable;
use App\Repository\TrelloWorkSpaceRepository;
use App\Repository\UserRepository;
use App\Traits\Telegram\Questions\HoursOnStudy;
use App\Traits\Telegram\Questions\StudySchedule;
use App\Traits\Telegram\Questions\StudySubject;
use App\Traits\Telegram\Questions\UserEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Helpers\WeekDayDates;
use App\Service\Trello\Boards\BoardClient;
use App\Repository\Trello\BoardRepository;
use App\Repository\Trello\CardRepository;
use App\Repository\Trello\ListRepository;
use App\Service\Trello\Cards\CardClient;
use App\Traits\Telegram\ResetUserAnswers;

class MessageHandler
{
    use StudySubject;
    use ResetUserAnswers;
    use HoursOnStudy;
    use StudySchedule;
    use UserEmail;

    public function __construct(
        private readonly TrelloWorkSpaceRepository $trelloWorkSpaceRepository,
        private readonly UserRepository $userRepository,
        private readonly BoardRepository $boardRepository,
        private readonly CardRepository $cardRepository,
        private readonly BoardClient $boardClient,
        private readonly CardClient $cardClient,
        private readonly ListRepository $listRepository,
        private readonly WeekDayDates $weekDayDates,
    ) {}

    public function handleMessages(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
        if ($this->boardRepository->userBoardWasCreated($userId)) {
            $this->updateChatState($userId, ChatStateEnum::USER_HAS_WORKSPACE->value);
        }

        try {
            $this->handleChatState($messageDto, $userId);
        } catch (Throwable $e) {
            Log::channel('telegram')->error('Something went wrong: ' . $e->getMessage());
            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.error'),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    private function handleChatState(TelegramMessageDto $messageDto, string $userId): void
    {
        $chatState = $this->getChatState($userId);

        switch ($chatState) {
            case ChatStateEnum::START->value:
                $this->updateChatState($userId, ChatStateEnum::SUBJECT_STUDY->value);
                $chatState = ChatStateEnum::SUBJECT_STUDY->value;
            case ChatStateEnum::SUBJECT_STUDY->value:
                $this->sendSubjectQuestion($messageDto);
                if ($this->acceptSubjectAnswer($messageDto)) {
                    $this->updateChatState($userId, ChatStateEnum::HOURS->value);
                    $this->sendHoursQuestion($messageDto);
                }

                break;
            case ChatStateEnum::HOURS->value:
                if ($this->acceptHoursAnswer($messageDto)) {
                    $this->updateChatState($userId, ChatStateEnum::SCHEDULE->value);
                    $this->sendScheduleQuestion($messageDto);
                }
                break;
            case ChatStateEnum::SCHEDULE->value:
                if ($this->acceptScheduleAnswer($messageDto)) {
                    $this->updateChatState($userId, ChatStateEnum::EMAIL->value);
                    $this->sendEmailQuestion($messageDto);
                }
                break;
            case ChatStateEnum::EMAIL->value:
                if ($this->acceptEmailAnswer($messageDto)) {
                    $this->updateChatState($userId, ChatStateEnum::FINISHED->value);

                    $dto = $this->prepareUserWorkspaceForCreating($userId);
                    dispatch(new CreateUserTrelloWorkspace($dto->workspace, $dto->user));

                    TelegramBotRequest::sendMessage([
                        'chat_id' => $messageDto->user->getChatId(),
                        'text' => __(
                            'bot_messages.trello_workspace_created', [
                                'name' => $messageDto->user->getFirstName()  . ' '
                                . $messageDto->user->getLastName()
                            ]
                        ),
                        'parse_mode' => 'Markdown'
                    ]);
                }
                break;
            case ChatStateEnum::USER_HAS_WORKSPACE->value:
                $trelloBoard = $this->boardRepository->getBoardByUserId($userId);
                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __('bot_messages.workspace_created', ['url' => $trelloBoard->url]),
                    'parse_mode' => 'Markdown'
                ]);
                break;
        }
    }

    public function getChatState(string $userId): int
    {
        return json_decode(Redis::get($userId . '_' . ChatStateEnum::class), true)['value'];
    }

    private function prepareUserWorkspaceForCreating(string $userId): UserWorkspaceDto
    {
        $userEmailInfo = json_decode(
            Redis::get($userId . '_' . UserEmailEnum::QUESTION->value), true);

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
