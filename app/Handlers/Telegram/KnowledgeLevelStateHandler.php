<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\GoalEnum;
use App\Enums\Telegram\KnowledgeLevelEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Service\OpenAi\KnowledgeLevelValidator;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Request as TelegramBotRequest;

class KnowledgeLevelStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly ToolsStateHandler $nextHandler,
        private readonly QuestionsRedisManager $questionsRedisManager,
        private readonly KnowledgeLevelValidator $knowledgeLevelValidator
    ) {}

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        $userId = $messageDto->user->_id;
        $previousAnswer = $this->questionsRedisManager->getPreviousAnswer($userId, GoalEnum::QUESTION->value);

        if ($chatState === ChatStateEnum::KNOWLEDGE_LEVEL->value && $previousAnswer) {
            $this->sendQuestion($messageDto);
            Log::channel('telegram')->info('Current knowledge level state: ' . $chatState);
            if ($this->acceptAnswer($messageDto)) {
                $messageDto->answer = null;
                $messageDto->callbackData = null;
                $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::TOOLS->value);

                $this->nextHandler->handle($messageDto, ChatStateEnum::TOOLS->value);
            }
        } else {
            Log::channel('telegram')->info('Go to tools state: ' . $chatState);
            $this->nextHandler->handle($messageDto, $chatState);
        }
    }

    private function sendQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->_id;
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

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
    {
        if (empty($messageDto->answer)) {
            return false;
        }
        
        $userId = $messageDto->user->_id;
        $subjectInfo = json_decode(Redis::get($userId.'_'.SubjectStudiesEnum::QUESTION->value), true);
        $validateKnowledgeLevel = $this->knowledgeLevelValidator->validateKnowledgeLevel($subjectInfo['current_answer'] ?? '', $messageDto->answer ?? '');

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
