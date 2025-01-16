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

    public function validateHoursAnswer(TelegramMessageDto $messageDto): void
    {
        $hoursOnStudyInfo = json_decode(Redis::get($messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value), true);

        if (!empty($messageDto->answer) && !$this->validateEmail($messageDto->answer) && $hoursOnStudyInfo['approved'] === 0) {

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __(
                    'bot_messages.wrong_hours',
                    ['hours' => $messageDto->answer]
                ),
            ]);
        }
    }

    public function acceptHoursAnswer(TelegramMessageDto $messageDto): void
    {
        $hoursOnStudyInfo = json_decode(Redis::get($messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value), true);

        if (!empty($messageDto->answer) && $hoursOnStudyInfo['approved'] === 0) {

            Redis::set(
                $messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value,
                json_encode(['current_answer' => $hoursOnStudyInfo['current_answer'], 'approved' => 1])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Hours on studying was sucessufully save✅'
            ]);
        }
    }

    private function validateHours(string $hours): bool
    {
        $pattern = "/^(0|[1-9]\d*)(\.\d+)?$/";
        return preg_match($pattern, $hours) ? true: false;
    }
}
