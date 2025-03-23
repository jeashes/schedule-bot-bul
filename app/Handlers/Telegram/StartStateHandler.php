<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Managers\Telegram\QuestionsRedisManager;

class StartStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly SubjectStateHandler $nextHandler,
        private readonly QuestionsRedisManager $questionsRedisManager
    ) { }

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        if ($chatState === ChatStateEnum::START->value) {
            $this->questionsRedisManager->updateChatState($messageDto->user->getId(), ChatStateEnum::SUBJECT_STUDY->value);
            $this->nextHandler->handle($messageDto, ChatStateEnum::SUBJECT_STUDY->value);
        }
    }
}