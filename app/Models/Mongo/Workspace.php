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
        'goal',
        'knowledge_level',
        'course_type',
        'tools',
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

    public function getGoal(): string
    {
        return $this->getAttribute('goal');
    }

    public function setGoal(string $value): void
    {
        $this->setAttribute('goal', $value);
    }

    public function getKnowledgeLevel(): string
    {
        return $this->getAttribute('knowledge_level');
    }

    public function setKnowledgeLevel(string $value): void
    {
        $this->setAttribute('knowledge_level', $value);
    }

    public function getCourseType(): int
    {
        return $this->getAttribute('course_type');
    }

    public function setCourseType(int $value): void
    {
        $this->setAttribute('course_type', $value);
    }

    public function getTools(): string
    {
        return $this->getAttribute('tools');
    }

    public function setTools(string $value): void
    {
        $this->setAttribute('tools', $value);
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
