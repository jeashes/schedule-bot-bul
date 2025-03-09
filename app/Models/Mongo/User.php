<?php

namespace App\Models\Mongo;

use Illuminate\Database\Eloquent\Relations\HasOne;
use MongoDB\Laravel\Eloquent\Model;

class User extends Model
{
    public $timestamps = true;

    protected $connection = 'mongodb';

    protected $collection = 'users';

    protected $fillable = [
        'username',
        'workspace_id',
        'first_name',
        'last_name',
        'chat_id',
        'language_code',
        'email',
        'goal',
        'knowledge_level',
        'course_type',
        'tools',
        'created_at',
        'updated_at',
    ];

    public function getId(): string
    {
        return $this->getAttribute('_id');
    }

    public function getUserName(): ?string
    {
        return $this->getAttribute('username');
    }

    public function setUserName(string $value): void
    {
        $this->setAttribute('username', $value);
    }

    public function getFirstName(): string
    {
        return $this->getAttribute('first_name');
    }

    public function setFirstName(string $value): void
    {
        $this->setAttribute('first_name', $value);
    }

    public function getLastName(): ?string
    {
        return $this->getAttribute('last_name');
    }

    public function setLastName(string $value): void
    {
        $this->setAttribute('lasts_name', $value);
    }

    public function getChatId(): int
    {
        return $this->getAttribute('chat_id');
    }

    public function setChatId(int $value): void
    {
        $this->setAttribute('chat_id', $value);
    }

    public function getLanguageCode(): string
    {
        return $this->getAttribute('language_code');
    }

    public function setLanguageCode(string $value): void
    {
        $this->setAttribute('language_code', $value);
    }

    public function getEmail(): ?string
    {
        return $this->getAttribute('email');
    }

    public function setEmail(string $value): void
    {
        $this->setAttribute('email', $value);
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

    public function workspace(): HasOne
    {
        return $this->hasOne(Workspace::class, 'workspace_id', '_id');
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function getWorkspaceId(): ?string
    {
        return $this->getAttribute('workspace_id');
    }

    public function setWorkspaceId(string $value): void
    {
        $this->setAttribute('workspace_id', $value);
    }
}
