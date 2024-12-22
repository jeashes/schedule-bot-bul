<?php

namespace App\Dto\Telegram;

use App\Models\Mongo\User as MongoUser;

class UserMessageDto
{
    public function __construct(
        public ?string $answer,
        public ?string $callbackData,
        public readonly MongoUser $user
    ) {
    }
}
