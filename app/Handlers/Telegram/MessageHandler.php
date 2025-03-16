<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Dto\UserWorkspaceDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\UserEmailEnum;
use App\Helpers\WeekDayDates;
use App\Jobs\CreateUserTrelloWorkspace;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Repository\Trello\BoardRepository;
use App\Repository\Trello\CardRepository;
use App\Repository\Trello\ListRepository;
use App\Repository\TrelloWorkSpaceRepository;
use App\Repository\UserRepository;
use App\Service\Trello\Boards\BoardClient;
use App\Service\Trello\Cards\CardClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Throwable;

class MessageHandler
{
    public function __construct(
        private readonly TrelloWorkSpaceRepository $trelloWorkSpaceRepository,
        private readonly UserRepository $userRepository,
        private readonly BoardRepository $boardRepository,
        private readonly CardRepository $cardRepository,
        private readonly BoardClient $boardClient,
        private readonly CardClient $cardClient,
        private readonly ListRepository $listRepository,
        private readonly WeekDayDates $weekDayDates,
        private readonly SubjectStateHandler $subjectStateHandler,
        private readonly GoalStateStateHandler $goalStateHandler,
        private readonly KnowledgeLevelStateHandler $knowledgeLevelStateHandler,
        private readonly ToolsStateHandler $toolsStateHandler,
        private readonly CourseTypeStateHandler $courseTypeStateHandler,
        private readonly ScheduleStateHandler $scheduleStateHandler,
        private readonly HoursStateHandler $hoursStateHandler,
        private readonly EmailStateHandler $emailStateHandler,
        private readonly QuestionsRedisManager $questionsRedisManager
    ) {}

    public function handleMessages(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
        if ($this->boardRepository->userBoardWasCreated($userId)) {
            $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::USER_HAS_WORKSPACE->value);
        }

        $state = $this->getChatState($userId);

        if (! $this->boardRepository->userBoardWasCreated($userId) && $state === ChatStateEnum::USER_HAS_WORKSPACE->value) {
            $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::START->value);
        }

        try {
            $this->handleChatState($messageDto, $userId);
        } catch (Throwable $e) {
            Log::channel('telegram')->error('Something went wrong: '.$e->getMessage().','.$e->getLine(), [
                'chat_state' => $state,
                'user_id' => $userId,
                'chat_id' => $messageDto->user->getChatId(),
                'user_has_workspace' => $this->boardRepository->userBoardWasCreated($userId),
                'stacktrace' => $e->getTraceAsString(),
            ]);
            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.error'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function handleChatState(TelegramMessageDto $messageDto, string $userId): void
    {
        $chatState = $this->getChatState($userId);

        switch ($chatState) {
            case ChatStateEnum::START->value:
                $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::SUBJECT_STUDY->value);

                $chatState = ChatStateEnum::SUBJECT_STUDY->value;
            case ChatStateEnum::SUBJECT_STUDY->value:
                $this->subjectStateHandler->sendSubjectQuestion($messageDto);

                if ($this->subjectStateHandler->acceptSubjectAnswer($messageDto)) {
                    $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::GOAL->value);

                    $this->goalStateHandler->sendGoalQuestion($messageDto);
                }
                break;
            case ChatStateEnum::GOAL->value:
                if ($this->goalStateHandler->acceptGoalAnswer($messageDto)) {
                    $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::KNOWLEDGE_LEVEL->value);

                    $this->knowledgeLevelStateHandler->sendKnowledgeLevelQuestion($messageDto);
                }
                break;
            case ChatStateEnum::KNOWLEDGE_LEVEL->value:
                if ($this->knowledgeLevelStateHandler->acceptKnowledgeLevelAnswer($messageDto)) {
                    $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::TOOLS->value);

                    $this->toolsStateHandler->sendToolsQuestion($messageDto);
                }
                break;
            case ChatStateEnum::TOOLS->value:
                if ($this->toolsStateHandler->acceptToolsAnswer($messageDto)) {
                    $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::COURSE_TYPE->value);

                    $this->courseTypeStateHandler->sendCourseTypeQuestion($messageDto);
                }
                break;
                case ChatStateEnum::COURSE_TYPE->value:
                if ($this->courseTypeStateHandler->acceptCourseTypeAnswer($messageDto)) {
                    $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::HOURS->value);

                    $this->hoursStateHandler->sendHoursQuestion($messageDto);
                }
                break;
            case ChatStateEnum::HOURS->value:
                if ($this->hoursStateHandler->acceptHoursAnswer($messageDto)) {
                    $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::SCHEDULE->value);

                    $this->scheduleStateHandler->sendScheduleQuestion($messageDto);
                }
                break;
            case ChatStateEnum::SCHEDULE->value:
                if ($this->scheduleStateHandler->acceptScheduleAnswer($messageDto)) {
                    $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::EMAIL->value);

                    $this->emailStateHandler->sendEmailQuestion($messageDto);
                }
                break;
            case ChatStateEnum::EMAIL->value:
                if ($this->emailStateHandler->acceptEmailAnswer($messageDto)) {
                    $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::FINISHED->value);

                    $dto = $this->prepareUserWorkspaceForCreating($userId);
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
                break;
            case ChatStateEnum::USER_HAS_WORKSPACE->value:
                $trelloBoard = $this->boardRepository->getBoardByUserId($userId);
                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __('bot_messages.workspace_created', ['url' => $trelloBoard->url]),
                    'parse_mode' => 'Markdown',
                ]);
                break;
        }
    }

    private function getChatState(string $userId): int
    {
        return json_decode(Redis::get($userId.'_'.ChatStateEnum::class), true)['value'];
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
