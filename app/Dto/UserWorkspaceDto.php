<?php

namespace App\Dto;

use App\Models\Mongo\User;
use App\Models\Mongo\Workspace;

class UserWorkspaceDto
{
    public function __construct(
        public readonly Workspace $workspace,
        public readonly User $user
    ) {}
}
