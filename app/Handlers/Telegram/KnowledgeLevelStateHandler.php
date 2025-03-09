<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Managers\Telegram\QuestionsRedisManager;
use Illuminate\Support\Facades\Redis;
use App\Enums\Telegram\KnowledgeLevelEnum;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Service\OpenAi\KnowledgeLevelValidator;

class KnowledgeLevelStateHandler
{
    public function __construct(
        private readonly QuestionsRedisManager $questionsRedisManager,
        private readonly KnowledgeLevelValidator $knowledgeLevelValidator
    ) {}

    public function sendKnowledgeLevelQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
        $knowledgeLevelInfo = json_decode(Redis::get($userId.'_'.KnowledgeLevelEnum::QUESTION->value), true);

        if (is_null($knowledgeLevelInfo['current_answer'])) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, KnowledgeLevelEnum::QUESTION->value);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.knowledge_level_question'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    public function acceptKnowledgeLevelAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();
        $validateKnowledgeLevel = $this->knowledgeLevelValidator->validateKnowledgeLevel($messageDto->answer ?? '');

        switch ($validateKnowledgeLevel) {
            case true:
                $this->questionsRedisManager->setAnswerForQuestion($userId, KnowledgeLevelEnum::QUESTION->value, $messageDto->answer, 1);

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => 'Your knowledge level was save✅',
                ]);

                return $validateKnowledgeLevel;

            case false:
                if (is_null($messageDto->answer)) {
                    return $validateKnowledgeLevel;
                }

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __('bot_messages.wrong_knowledge_level'),
                ]);

                return $validateKnowledgeLevel;
        }
    }
}
