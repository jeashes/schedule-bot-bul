<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\GoalEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Service\OpenAi\GoalValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;

class GoalStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly KnowledgeLevelStateHandler $nextHandler,
        private readonly QuestionsRedisManager $questionsRedisManager,
        private readonly GoalValidator $goalValidator,
    ) {}

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        $userId = $messageDto->user->getId();
        $previousAnswer = $this->questionsRedisManager->getPreviousAnswer($userId, SubjectStudiesEnum::QUESTION->value);
        if ($chatState === ChatStateEnum::GOAL->value && $previousAnswer) {
            $this->sendQuestion($messageDto);
            Log::channel('telegram')->info('Current goal state: '.$chatState);
            if ($this->acceptAnswer($messageDto)) {
                $messageDto->answer = null;
                $messageDto->callbackData = null;
                $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::KNOWLEDGE_LEVEL->value);

                $this->nextHandler->handle($messageDto, ChatStateEnum::KNOWLEDGE_LEVEL->value);
            }
        } else {
            Log::channel('telegram')->info('Go to knowledge level state: '.$chatState);
            $this->nextHandler->handle($messageDto, $chatState);
        }
    }

    private function sendQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
        $goalInfo = json_decode(Redis::get($userId.'_'.GoalEnum::QUESTION->value), true);

        if (is_null($goalInfo['current_answer'])) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, GoalEnum::QUESTION->value);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => $this->questionsRedisManager->getBotPhraseByKey($messageDto->languageCode, 'goal_question'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
    {
        if (empty($messageDto->answer)) {
            return false;
        }

        $userId = $messageDto->user->getId();
        $subjectInfo = json_decode(Redis::get($userId.'_'.SubjectStudiesEnum::QUESTION->value), true);
        $validateGoal = $this->goalValidator->validateLearnGoal($subjectInfo['current_answer'] ?? '', $messageDto->answer ?? '');

        switch ($validateGoal) {
            case true:
                $this->questionsRedisManager->setAnswerForQuestion($userId, GoalEnum::QUESTION->value, $messageDto->answer, 1);

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => $this->questionsRedisManager->getBotPhraseByKey($messageDto->languageCode, 'goal_saved'),
                ]);

                return $validateGoal;

            case false:
                if (is_null($messageDto->answer)) {
                    return $validateGoal;
                }

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => $this->questionsRedisManager->getBotPhraseByKey($messageDto->languageCode, 'wrong_learn_goal'),
                ]);

                return $validateGoal;
        }
    }
}
