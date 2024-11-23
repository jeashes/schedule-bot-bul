<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Casts\ObjectId;
use MongoDB\Laravel\Eloquent\Model;

class Workspace extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'workspace';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'time_on_schedule',
        'pace_level',
        'task_ids'
    ];

    public function getId(): ObjectId
    {
        return $this->getAttribute('_id');
    }

    public function getTimeOnSchedule(): float
    {
        return $this->getAttribute('time_on_schedule');
    }

    public function setTimeOnSchedule(float $value): void
    {
        $this->setAttribute('time_on_schedule', $value);
    }

    public function getPaceLevel(): string
    {
        return $this->getAttribute('pace_level');
    }

    public function setPaceLevel(string $value): void
    {
        $this->setAttribute('pace_level', $value);
    }

    public function getTaskIds(): array
    {
        return $this->getAttribute('task_ids');
    }

    private function setTaskIds(array $value): void
    {
        $this->setAttribute('task_ids', $value);
    }

    public function addTaskId(ObjectId $value): void
    {
        $currentTasks = $this->task_ids ?? [];
        if (!in_array((string)$value, array_map('strval', $currentTasks))) {
            $currentTasks[] = $value;
            $this->setTaskIds($currentTasks);
        }
    }
}
