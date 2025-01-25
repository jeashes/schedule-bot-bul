<?php

namespace App\Managers\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Http;

class EmailManager
{
    static public function sendEmailQuestion(TelegramMessageDto $messageDto): void
    {
        Http::post('api/email/send-question', [
            'answer' => $messageDto->answer,
            'callbackData' => $messageDto->callbackData,
            'user' => $messageDto->user->toArray()
            ]
        );
    }

    static public function acceptEmailQuestion(TelegramMessageDto $messageDto): bool
    {
        $response = Http::post('api/email/accept-answer', [
            'answer' => $messageDto->answer,
            'callbackData' => $messageDto->callbackData,
            'user' => $messageDto->user->toArray()
            ]);

        return $response->body()['success'];
    }
}
