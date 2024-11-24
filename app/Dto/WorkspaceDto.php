<?php

namespace App\Dto;

class WorkspaceDto
{
    public function __construct(
        private readonly string $name,
        private readonly float $timeOnSchedule,
        private readonly string $paceLevel,
        private readonly array $taskIds
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTimeOnSchedule(): float
    {
        return $this->timeOnSchedule;
    }

    public function getPaceLevel(): string
    {
        return $this->paceLevel;
    }

    public function getTaskIds(): array
    {
        return $this->taskIds;
    }
}
