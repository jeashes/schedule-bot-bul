<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Class Workspace
 *
 * Represents a workspace entity stored in a MongoDB collection.
 *
 * @property string $name             The name of the workspace.
 * @property float $time_on_schedule   The amount of time scheduled for the workspace.
 * @property int $schedule            The schedule identifier or value.
 * @property string[] $task_ids       An array of associated task IDs.
 * @property string $goal             The goal or objective of the workspace.
 * @property string $knowledge_level   The knowledge level associated with the workspace.
 * @property int $course_type         The type of course associated with the workspace.
 * @property bool $is_active          Indicates if the workspace is active.
 * @property string|null $tools       The tools associated with the workspace.
 * @property Carbon $created_at       Created date.
 * @property Carbon $updated_at       Updated date.
 *
 * @method void addTaskId(string $taskId) Adds a task ID to the workspace's task_ids string[].
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|Workspace query()
 */
class Workspace extends Model
{
    public $timestamps = true;

    protected $connection = 'mongodb';

    protected $collection = 'workspaces';

    protected $fillable = [
        'name',
        'time_on_schedule',
        'schedule',
        'task_ids',
        'goal',
        'knowledge_level',
        'course_type',
        'is_active',
        'tools',
    ];

    protected $casts = [
        '_id' => 'string',
        'is_active' => 'boolean',
        'time_on_schedule' => 'float',
        'schedule' => 'integer',
        'course_type' => 'integer',
        'task_ids' => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime'
    ];

    /**
     * Adds a task ID to the 'task_ids' array attribute of the workspace.
     *
     * @param string $taskId The ID of the task to add.
     * @return void
     */
    public function addTaskId(string $taskId): void
    {
        $this->push('task_ids', $taskId, true);
    }
}
