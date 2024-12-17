<?php

namespace App\Dto;

use App\Models\Mongo\User as MongoUser;

class TelegramMessageDto
{
    public function __construct(
        public ?string $answer,
        public ?string $callbackData,
        public readonly MongoUser $user
    ) {
    }
}
