<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\UserEmailEnum;
use App\Managers\Telegram\QuestionsRedisManager;

class EmailStateHandler
{
    static public function sendEmailQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
        $userEmailInfo = json_decode(Redis::get($userId . '_' . UserEmailEnum::QUESTION->value), true);

        if (is_null($userEmailInfo['current_answer'])) {

            QuestionsRedisManager::setAnswerForQuestion($userId, UserEmailEnum::QUESTION->value);

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

    static public function acceptEmailAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();
        $validatedEmail = self::validateEmail($messageDto->answer);

        switch ($validatedEmail) {
            case true:
                QuestionsRedisManager::setAnswerForQuestion($userId, UserEmailEnum::QUESTION->value, $messageDto->answer, 1);

                return $validatedEmail;
            case false:
                TelegramBotRequest::sendMessage([
                    'chat_id' => $data['user']['chat_id'],
                    'text' => __(
                        'bot_messages.wrong_email',
                        ['email' => $data['answer']]
                    ),
                ]);

                return $validatedEmail;
        }

        return false;
    }

    static private function validateEmail(?string $email): bool
    {
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
        return (preg_match($pattern, $email ?? '')) ? true : false;
    }
}
