<?php

namespace App\Managers\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Http;

class ScheduleManager
{
    static public function sendScheduleQuestion(TelegramMessageDto $messageDto): void
    {
        Http::post('api/schedule/send-question', [
            'answer' => $messageDto->answer,
            'callbackData' => $messageDto->callbackData,
            'user' => $messageDto->user->toArray()
            ]
        );
    }

    static public function acceptScheduleQuestion(TelegramMessageDto $messageDto): bool
    {
        $response = Http::post('api/schedule/accept-answer', [
            'answer' => $messageDto->answer,
            'callbackData' => $messageDto->callbackData,
            'user' => $messageDto->user->toArray()
            ]);

        return $response->body()['success'];
    }
}
