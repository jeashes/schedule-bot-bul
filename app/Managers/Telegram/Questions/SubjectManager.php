<?php

namespace App\Managers\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Http;

class SubjectManager
{
    static public function sendSubjectQuestion(TelegramMessageDto $messageDto): void
    {
        Http::post('/subject/send-question', [
            'answer' => $messageDto->answer,
            'callbackData' => $messageDto->callbackData,
            'user' => $messageDto->user->toArray()
            ]
        );
    }

    static public function acceptSubjectQuestion(TelegramMessageDto $messageDto): bool
    {
        $response = Http::post('/subject/accept-answer', [
            'answer' => $messageDto->answer,
            'callbackData' => $messageDto->callbackData,
            'user' => $messageDto->user->toArray()
            ]);

        return $response->body()['success'];
    }
}
