<?php

namespace App\Repository;

use App\Enums\Telegram\CourseTypeEnum;
use App\Enums\Telegram\GoalEnum;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\KnowledgeLevelEnum;
use App\Enums\Telegram\ScheduleEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Enums\Telegram\ToolsEnum;
use App\Models\Mongo\User as MongoUser;
use App\Models\Mongo\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TrelloWorkSpaceRepository
{
    public function createWorkspaceByUserId(array $workspaceParams, string $userId): Workspace
    {
        return DB::transaction(function () use ($workspaceParams, $userId) {
            $workspace = Workspace::query()->firstOrCreate($workspaceParams, []);

            $user = MongoUser::query()->findOrFail($userId);

            $user->update(['workspace_id' => $workspace->_id]);

            return $workspace;
        }, 1);
    }

    public function getWorkspaceParamsFromRedis(string $userId): array
    {
        $subjectStudiesInfo = json_decode(Redis::get($userId.'_'.SubjectStudiesEnum::QUESTION->value), true);
        $hoursOnStudyInfo = json_decode(Redis::get($userId.'_'.HoursOnStudyEnum::QUESTION->value), true);
        $scheduleInfo = json_decode(Redis::get($userId.'_'.ScheduleEnum::QUESTION->value), true);
        $goalInfo = json_decode(Redis::get($userId.'_'.GoalEnum::QUESTION->value), true);
        $toolsInfo = json_decode(Redis::get($userId.'_'.ToolsEnum::QUESTION->value), true);
        $knowledgeLevelInfo = json_decode(Redis::get($userId.'_'.KnowledgeLevelEnum::QUESTION->value), true);
        $courseTypeInfo = json_decode(Redis::get($userId.'_'.CourseTypeEnum::QUESTION->value), true);

        return [
            'name' => $subjectStudiesInfo['current_answer'],
            'time_on_schedule' => (float) $hoursOnStudyInfo['current_answer'],
            'schedule' => $scheduleInfo['current_answer'],
            'goal' => $goalInfo['current_answer'],
            'tools' => $toolsInfo['current_answer'],
            'knowledge_level' => $knowledgeLevelInfo['current_answer'],
            'course_type' => $courseTypeInfo['current_answer'],
            'task_ids' => [],
        ];
    }
}
