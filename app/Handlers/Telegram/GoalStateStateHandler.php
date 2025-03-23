<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Managers\Telegram\QuestionsRedisManager;
use Illuminate\Support\Facades\Redis;
use App\Enums\Telegram\GoalEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Service\OpenAi\GoalValidator;
use App\Interfaces\Telegram\StateHandlerInterface;

class GoalStateStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly KnowledgeLevelStateHandler $nextHandler,
        private readonly QuestionsRedisManager $questionsRedisManager,
        private readonly GoalValidator $goalValidator,
    ) {}

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        if ($chatState === ChatStateEnum::GOAL->value) {
            $this->sendQuestion($messageDto);
            if ($this->acceptAnswer($messageDto)) {
                $this->questionsRedisManager->updateChatState($messageDto->user->getId(), ChatStateEnum::KNOWLEDGE_LEVEL->value);

                $this->nextHandler->handle($messageDto, ChatStateEnum::KNOWLEDGE_LEVEL->value);
            }
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
                'text' => __('bot_messages.goal_question'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();
        $subjectInfo = json_decode(Redis::get($userId.'_'.SubjectStudiesEnum::QUESTION->value), true);
        $validateGoal = $this->goalValidator->validateLearnGoal($subjectInfo['current_answer'] ?? '', $messageDto->answer ?? '');

        switch ($validateGoal) {
            case true:
                $this->questionsRedisManager->setAnswerForQuestion($userId, GoalEnum::QUESTION->value, $messageDto->answer, 1);

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => 'Your study goal was save✅',
                ]);

                return $validateGoal;

            case false:
                if (is_null($messageDto->answer)) {
                    return $validateGoal;
                }

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __('bot_messages.wrong_learn_goal'),
                ]);

                return $validateGoal;
        }
    }
}
