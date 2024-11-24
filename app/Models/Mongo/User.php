<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Casts\ObjectId;

class User extends Model
{
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
        'created_at',
        'updated_at'
    ];

    public $timestamps = true;

    public function getId(): ObjectId
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

    public function getWorkspaceId(): ?ObjectId
    {
        return $this->getAttribute('workspace_id');
    }

    public function setWorkspaceId(ObjectId $value): void
    {
        $this->setAttribute('workspace_id', $value);
    }
}
