<?php

namespace App\Traits\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Helpers\TelegramHelper;

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
        if (!$this->validateEmail($messageDto->answer) && TelegramHelper::notEmptyNotApprovedMessage($messageDto, HoursOnStudyEnum::QUESTION->value)) {

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
        if (TelegramHelper::notEmptyNotApprovedMessage($messageDto, HoursOnStudyEnum::QUESTION->value)) {

            Redis::set(
                $messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value,
                json_encode(['current_answer' =>  $messageDto->answer, 'approved' => 1])
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
