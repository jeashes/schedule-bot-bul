<?php

namespace App\Http\Controllers\SubControllers\Telegram;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\HoursOnStudyEnum;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HourOnStudyController
{
    #[Route('POST', '/hours/send-question')]
    public function sendHoursQuestion(TelegramMessageDto $messageDto): Response
    {
        $hoursOnStudyInfo = json_decode(Redis::get($messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value), true);

        if (is_null($hoursOnStudyInfo['current_answer'])) {
            Redis::set(
                $messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value,
                json_encode(['current_answer' => '','approved' => 0])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.total_hours_on_study')
            ]);

            return response()->noContent();
        }

        return response()->noContent();
    }

    #[Route('POST', '/hours/accept-answer')]
    public function acceptHoursAnswer(TelegramMessageDto $messageDto): JsonResponse
    {
        $userId = $messageDto->user->getId();
        $validateHours = $this->validateHours($messageDto->answer);

        switch ($validateHours) {
            case true:
                Redis::set(
                    $userId . '_' . HoursOnStudyEnum::QUESTION->value,
                    json_encode(['current_answer' =>  $messageDto->answer, 'approved' => 1])
                );

                $messageDto->answer = null;

                TelegramBotRequest::sendMessage([
                    'chat_id' => $userId,
                    'text' => 'Hours on studying was sucessufully save✅'
                ]);
                return response()->json(['success' => $validateHours]);
            case false:
                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __(
                        'bot_messages.wrong_hours',
                        ['hours' => $messageDto->answer]
                    ),
                ]);
                return response()->json(['success' => $validateHours]);
        }

        return response()->json(['success' => false]);
    }

    private function validateHours(?string $hours): bool
    {
        $pattern = "/^(0|[1-9]\d*)(\.\d+)?$/";
        return preg_match($pattern, $hours ?? '') ? true: false;
    }
}
