<?php

namespace App\Dto;

use MongoDB\Laravel\Eloquent\Casts\ObjectId;

class UserDto
{
    public function __construct(
        private readonly string $username,
        private readonly ?ObjectId $workspaceId,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly int $chatId,
        private readonly string $languageCode,
        private readonly ?string $email
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }
}
