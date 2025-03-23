<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\KnowledgeLevelEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Service\OpenAi\KnowledgeLevelValidator;
use Illuminate\Support\Facades\Redis;
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
        if ($chatState === ChatStateEnum::KNOWLEDGE_LEVEL->value) {
            $this->sendQuestion($messageDto);
            if ($this->acceptAnswer($messageDto)) {
                $this->questionsRedisManager->updateChatState($messageDto->user->getId(), ChatStateEnum::TOOLS->value);

                $this->nextHandler->handle($messageDto, ChatStateEnum::TOOLS->value);
            } else {
                $this->nextHandler->handle($messageDto, ChatStateEnum::TOOLS->value);
            }
        }
    }

    private function sendQuestion(TelegramMessageDto $messageDto): void
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

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();
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
