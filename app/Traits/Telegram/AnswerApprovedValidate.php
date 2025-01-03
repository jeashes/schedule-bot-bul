<?php

namespace App\Traits\Telegram;

use Illuminate\Support\Facades\Redis;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\ScheduleEnum;
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

    private function isScheduleApproved(string $userId): bool
    {
        $scheduleInfo = json_decode(Redis::get($userId . '_' . ScheduleEnum::QUESTION->value), true);
        return !empty($scheduleInfo['current_answer']) && !empty($scheduleInfo['approved']);
    }

    private function isUserEmailApproved(string $userId): bool
    {
        $userEmailInfo = json_decode(Redis::get($userId . '_' . UserEmailEnum::QUESTION->value), true);
        return !empty($userEmailInfo['current_answer']) && !empty($userEmailInfo['approved']);
    }
}
