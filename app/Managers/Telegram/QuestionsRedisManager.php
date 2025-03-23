<?php

namespace App\Managers\Telegram;

use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\CourseTypeEnum;
use App\Enums\Telegram\GoalEnum;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\KnowledgeLevelEnum;
use App\Enums\Telegram\ScheduleEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Enums\Telegram\ToolsEnum;
use App\Enums\Telegram\UserEmailEnum;
use Illuminate\Support\Facades\Redis;

class QuestionsRedisManager
{
    public function resetUserAnswers(string $userId): void
    {
        $this->removeOldAnswers($userId);

        $this->resetUserAnswer($userId, SubjectStudiesEnum::QUESTION->value);

        $this->resetUserAnswer($userId, GoalEnum::QUESTION->value);

        $this->resetUserAnswer($userId, KnowledgeLevelEnum::QUESTION->value);

        $this->resetUserAnswer($userId, ToolsEnum::QUESTION->value);

        $this->resetUserAnswer($userId, CourseTypeEnum::QUESTION->value);

        $this->resetUserAnswer($userId, HoursOnStudyEnum::QUESTION->value);

        $this->resetUserAnswer($userId, ScheduleEnum::QUESTION->value);

        $this->resetUserAnswer($userId, UserEmailEnum::QUESTION->value);

        $this->updateChatState($userId, ChatStateEnum::START->value);
    }

    public function updateChatState(string $userId, int $chatState): void
    {
        Redis::set($userId.'_'.ChatStateEnum::class, json_encode(['value' => $chatState]));
    }

    public function resetUserAnswer(string $userId, string $question): void
    {
        Redis::set($userId.'_'.$question, json_encode(['current_answer' => null, 'approved' => null]));
    }

    public function setAnswerForQuestion(string $userId, string $question, string $answer = '', int $approved = 0): void
    {
        Redis::set($userId.'_'.$question, json_encode(['current_answer' => $answer, 'approved' => $approved]));
    }

    private function removeOldAnswers(string $userId): void
    {
        Redis::del($userId.'_'.SubjectStudiesEnum::QUESTION->value);
        Redis::del($userId.'_'.GoalEnum::QUESTION->value);
        Redis::del($userId.'_'.KnowledgeLevelEnum::QUESTION->value);
        Redis::del($userId.'_'.ToolsEnum::QUESTION->value);
        Redis::del($userId.'_'.CourseTypeEnum::QUESTION->value);
        Redis::del($userId.'_'.HoursOnStudyEnum::QUESTION->value);
        Redis::del($userId.'_'.ScheduleEnum::QUESTION->value);
        Redis::del($userId.'_'.UserEmailEnum::QUESTION->value);
        Redis::del($userId.'_'.ChatStateEnum::class);
    }
}
