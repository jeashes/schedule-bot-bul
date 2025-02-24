<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class Workspace extends Model
{
    public $timestamps = true;

    protected $connection = 'mongodb';

    protected $collection = 'workspace';

    protected $fillable = [
        'name',
        'time_on_schedule',
        'schedule',
        'task_ids',
    ];

    public function getId(): string
    {
        return $this->getAttribute('_id');
    }

    public function getName(): string
    {
        return $this->getAttribute('name');
    }

    public function setName(string $value): void
    {
        $this->setAttribute('name', $value);
    }

    public function getTimeOnSchedule(): float
    {
        return $this->getAttribute('time_on_schedule');
    }

    public function setTimeOnSchedule(float $value): void
    {
        $this->setAttribute('time_on_schedule', $value);
    }

    public function getSchedule(): int
    {
        return $this->getAttribute('schedule');
    }

    public function setSchedule(int $value): void
    {
        $this->setAttribute('schedule', $value);
    }

    public function getTaskIds(): array
    {
        return $this->getAttribute('task_ids');
    }

    public function addTaskId(string $value): void
    {
        $currentTasks = $this->task_ids ?? [];
        if (! in_array((string) $value, array_map('strval', $currentTasks))) {
            $currentTasks[] = $value;
            $this->setTaskIds($currentTasks);
            $this->save();
        }
    }

    private function setTaskIds(array $value): void
    {
        $this->setAttribute('task_ids', $value);
    }
}
