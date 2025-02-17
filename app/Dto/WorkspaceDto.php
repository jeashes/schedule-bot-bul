<?php

namespace App\Dto;

class WorkspaceDto
{
    public function __construct(
        public readonly string $name,
        public readonly float $timeOnSchedule,
        public readonly int $schedule,
        public readonly array $taskIds
    ) {}
}
