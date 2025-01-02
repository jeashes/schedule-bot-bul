<?php

namespace App\Traits\Telegram;

use Illuminate\Support\Facades\Redis;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\PaceLevelEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Enums\Telegram\UserEmailEnum;

trait AnswerApprovedValidate
{
    private function isSubjectStudyApproved(string $userId): bool
    {
        $subjectStudiesInfo = json_decode(Redis::get($userId . '_' . SubjectStudiesEnum::QUESTION->value), true);
        return !empty($subjectStudiesInfo['current_answer']) && !empty($subjectStudiesInfo['approved']);
    }

    private function isHoursForStudyApproved(string $userId): bool
    {
        $hoursOnStudyInfo = json_decode(Redis::get($userId . '_' . HoursOnStudyEnum::QUESTION->value), true);
        return !empty($hoursOnStudyInfo['current_answer']) && !empty($hoursOnStudyInfo['approved']);
    }

    private function isPaceLevelApproved(string $userId): bool
    {
        $paceLevelInfo = json_decode(Redis::get($userId . '_' . PaceLevelEnum::QUESTION->value), true);
        return !empty($paceLevelInfo['current_answer']) && !empty($paceLevelInfo['approved']);
    }

    private function isUserEmailApproved(string $userId): bool
    {
        $userEmailInfo = json_decode(Redis::get($userId . '_' . UserEmailEnum::QUESTION->value), true);
        return !empty($userEmailInfo['current_answer']) && !empty($userEmailInfo['approved']);
    }
}
