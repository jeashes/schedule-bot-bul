<?php

namespace App\Dto;

use MongoDB\Laravel\Eloquent\Casts\ObjectId;

class UserDto
{
    public function __construct(
        public readonly string $firstName,
        public readonly int $chatId,
        public readonly int $telegramId,
        public readonly string $languageCode,
        public readonly ?string $username = null,
        public readonly ?string $lastName = null,
        public readonly ?string $email = null,
        public readonly ?ObjectId $workspaceId = null,
    ) {}
}
