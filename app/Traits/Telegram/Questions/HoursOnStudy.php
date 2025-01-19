<?php

namespace App\Traits\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\HoursOnStudyEnum;

trait HoursOnStudy
{
    public function sendHoursQuestion(TelegramMessageDto $messageDto): void
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
        }
    }

    public function acceptHoursAnswer(TelegramMessageDto $messageDto): bool
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
                return $validateHours;

            case false:
                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __(
                        'bot_messages.wrong_hours',
                        ['hours' => $messageDto->answer]
                    ),
                ]);
                return $validateHours;
        }

        return false;
    }

    private function validateHours(?string $hours): bool
    {
        $pattern = "/^(0|[1-9]\d*)(\.\d+)?$/";
        return preg_match($pattern, $hours ?? '') ? true: false;
    }
}
