<?php

namespace App\Managers\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Http;

class HoursManager
{
    static public function sendHoursQuestion(TelegramMessageDto $messageDto): void
    {
        Http::post('api/hours/send-question', [
            'answer' => $messageDto->answer,
            'callbackData' => $messageDto->callbackData,
            'user' => $messageDto->user->toArray()
            ]
        );
    }

    static public function acceptHoursQuestion(TelegramMessageDto $messageDto): bool
    {
        $response = Http::post('api/hours/accept-answer', [
            'answer' => $messageDto->answer,
            'callbackData' => $messageDto->callbackData,
            'user' => $messageDto->user->toArray()
            ]);

        return $response->body()['success'];
    }
}
