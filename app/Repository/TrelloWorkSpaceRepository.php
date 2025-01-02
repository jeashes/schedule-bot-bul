<?php

namespace App\Repository;

use App\Models\Mongo\User as MongoUser;
use App\Models\Mongo\Workspace;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\PaceLevelEnum;
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
        $subjectStudiesInfo = json_decode(Redis::get($userId . '_' . SubjectStudiesEnum::QUESTION->value), true);
        $hoursOnStudyInfo = json_decode(Redis::get($userId . '_' . HoursOnStudyEnum::QUESTION->value), true);
        $paceLevelInfo = json_decode(Redis::get($userId . '_' . PaceLevelEnum::QUESTION->value), true);

        return [
            'name' => $subjectStudiesInfo['current_answer'],
            'time_on_scedule' => (int)$hoursOnStudyInfo['current_answer'],
            'pace_level' => $paceLevelInfo['current_answer'],
            'task_ids' => []
        ];
    }
}
