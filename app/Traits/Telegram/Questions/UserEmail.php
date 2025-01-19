<?php

namespace App\Traits\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\UserEmailEnum;

trait UserEmail
{
    public function sendEmailQuestion(TelegramMessageDto $messageDto): void
    {
        $userEmailInfo = json_decode(Redis::get($messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value), true);

        if (is_null($userEmailInfo['current_answer'])) {
            Redis::set(
                $messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value,
                json_encode(['current_answer' => '', 'approved' => 0])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __(
                    'bot_messages.ask_email',
                    ['triesCount' => 3]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function acceptEmailAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();
        $validatedEmail = $this->validateEmail($messageDto->answer);

        switch ($validatedEmail) {
            case true:
                Redis::set(
                    $userId . '_' . UserEmailEnum::QUESTION->value,
                    json_encode(['current_answer' => $messageDto->answer, 'approved' => 1])
                );

                $messageDto->answer = null;
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
