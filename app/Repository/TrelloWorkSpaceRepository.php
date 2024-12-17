<?php

namespace App\Repository;

use App\Models\Mongo\User as MongoUser;
use App\Models\Mongo\Workspace;

class TrelloWorkSpaceRepository
{
    public function createWorkspaceByUserId(array $workspaceParams, string $userId): void
    {
        $workspace = Workspace::firstOrCreate($workspaceParams, []);
        MongoUser::find($userId)
            ->update(['workspace_id' => $workspace->getId()]);
    }
}
