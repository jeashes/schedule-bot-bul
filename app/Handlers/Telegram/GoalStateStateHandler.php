<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Managers\Telegram\QuestionsRedisManager;
use Illuminate\Support\Facades\Redis;
use App\Enums\Telegram\GoalEnum;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Service\OpenAi\GoalValidator;

class GoalStateStateHandler
{
    public function __construct(
        private readonly QuestionsRedisManager $questionsRedisManager,
        private readonly GoalValidator $goalValidator,
    ) {}

    public function sendGoalQuestion(TelegramMessageDto $messageDto): void
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

    public function acceptGoalAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();
        $validateGoal = $this->goalValidator->validateLearnGoal($messageDto->answer ?? '');

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
