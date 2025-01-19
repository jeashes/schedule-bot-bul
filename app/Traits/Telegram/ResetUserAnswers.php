<?php

namespace App\Traits\Telegram;

use App\Enums\Telegram\ChatStateEnum;
use Illuminate\Support\Facades\Redis;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\ScheduleEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Enums\Telegram\UserEmailEnum;

trait ResetUserAnswers
{
    private function resetUserAnswers(string $userId): void
    {
        $this->removeOldAnswers($userId);

        Redis::set(
            $userId . '_' . SubjectStudiesEnum::QUESTION->value,
            json_encode([
                'current_answer' => null,
                'approved' => null
            ])
        );

        Redis::set(
            $userId . '_' . HoursOnStudyEnum::QUESTION->value,
            json_encode([
                'current_answer' => null,
                'approved' => null
            ])
        );

        Redis::set(
            $userId . '_' . ScheduleEnum::QUESTION->value,
            json_encode([
                'current_answer' => null,
                'approved' => null
            ])
        );

        Redis::set(
            $userId . '_' . UserEmailEnum::QUESTION->value,
            json_encode([
                'current_answer' => null,
                'approved' => null
            ])
        );

        Redis::set(
            $userId . '_' . ChatStateEnum::class,
            json_encode([
                'value' => 0
            ])
        );
    }

    private function removeOldAnswers(string $userId): void
    {
        Redis::del($userId . '_' . SubjectStudiesEnum::QUESTION->value);
        Redis::del($userId . '_' . HoursOnStudyEnum::QUESTION->value);
        Redis::del($userId . '_' . ScheduleEnum::QUESTION->value);
        Redis::del($userId . '_' . UserEmailEnum::QUESTION->value);
        Redis::del($userId . '_' . ChatStateEnum::class);
    }

    private function updateChatState(string $userId, int $chatState): void
    {
        Redis::set($userId . '_' . ChatStateEnum::class, json_encode([ 'value' => $chatState]));
    }
}
