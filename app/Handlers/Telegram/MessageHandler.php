<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Repository\Trello\BoardRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Throwable;

class MessageHandler
{
    public function __construct(
        private readonly BoardRepository $boardRepository,
        private readonly StartStateHandler $startStateHandler,
        private readonly QuestionsRedisManager $questionsRedisManager
    ) {}

    public function handleMessages(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->_id;
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
                'chat_id' => $messageDto->user->chat_id,
                'user_has_workspace' => $this->boardRepository->userBoardWasCreated($userId),
                'stacktrace' => $e->getTraceAsString(),
            ]);
            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->chat_id,
                'text' => __('bot_messages.error'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function handleChatState(TelegramMessageDto $messageDto, string $userId): void
    {
        $chatState = $this->getChatState($userId);
        if ($chatState !== ChatStateEnum::USER_HAS_WORKSPACE->value) {
            $this->startStateHandler->handle($messageDto, $chatState);
        } else {
            $trelloBoard = $this->boardRepository->getBoardByUserId($userId);
            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->chat_id,
                'text' => __('bot_messages.workspace_created', ['url' => $trelloBoard->url]),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function getChatState(string $userId): int
    {
        return json_decode(Redis::get($userId.'_'.ChatStateEnum::class), true)['value'];
    }
}
