<?php

namespace App\Repository;

use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\ScheduleEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Models\Mongo\User as MongoUser;
use App\Models\Mongo\Workspace;
use Illuminate\Support\Facades\Redis;

class TrelloWorkSpaceRepository
{
    public function createWorkspaceByUserId(array $workspaceParams, string $userId): Workspace
    {
        $workspace = Workspace::firstOrCreate($workspaceParams, []);
        MongoUser::find($userId)->update(['workspace_id' => $workspace->getId()]);

        return $workspace;
    }

    public function getWorkspaceParamsFromRedis(string $userId): array
    {
        $subjectStudiesInfo = json_decode(Redis::get($userId.'_'.SubjectStudiesEnum::QUESTION->value), true);
        $hoursOnStudyInfo = json_decode(Redis::get($userId.'_'.HoursOnStudyEnum::QUESTION->value), true);
        $scheduleInfo = json_decode(Redis::get($userId.'_'.ScheduleEnum::QUESTION->value), true);

        return [
            'name' => $subjectStudiesInfo['current_answer'],
            'time_on_schedule' => (float) $hoursOnStudyInfo['current_answer'],
            'schedule' => $scheduleInfo['current_answer'],
            'task_ids' => [],
        ];
    }
}
