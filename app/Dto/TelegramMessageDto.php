<?php

namespace App\Dto;

use App\Models\Mongo\User as MongoUser;

class TelegramMessageDto
{
    public function __construct(
        public readonly ?string $answer,
        public readonly ?string $callbackData,
        public readonly MongoUser $user
    ) {
    }
}
