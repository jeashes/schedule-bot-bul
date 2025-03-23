<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\UserEmailEnum;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Enums\Telegram\ScheduleEnum;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;

class EmailStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly QuestionsRedisManager $questionsRedisManager
    ) {}

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        $userId = $messageDto->user->getId();
        $previousAnswer = $this->questionsRedisManager->getPreviousAnswer($userId, ScheduleEnum::QUESTION->value);

        if ($chatState === ChatStateEnum::EMAIL->value && $previousAnswer) {
            $this->sendQuestion($messageDto);
            if ($this->acceptAnswer($messageDto)) {
                $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::FINISHED->value);
            }
        }
    }

    private function sendQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
        $userEmailInfo = json_decode(Redis::get($userId.'_'.UserEmailEnum::QUESTION->value), true);

        if (is_null($userEmailInfo['current_answer'])) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, UserEmailEnum::QUESTION->value);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __(
                    'bot_messages.ask_email',
                    ['triesCount' => 3]
                ),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();
        $validatedEmail = $this->validateEmail($messageDto->answer);

        switch ($validatedEmail) {
            case true:
                $this->questionsRedisManager->setAnswerForQuestion($userId, UserEmailEnum::QUESTION->value, $messageDto->answer, 1);

                return $validatedEmail;
            case false:
                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __(
                        'bot_messages.wrong_email',
                        ['email' => $messageDto->answer]
                    ),
                ]);

                return $validatedEmail;
        }

        return false;
    }

    private function validateEmail(?string $email): bool
    {
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        return (preg_match($pattern, $email ?? '')) ? true : false;
    }
}
