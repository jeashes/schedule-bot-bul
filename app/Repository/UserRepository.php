<?php

namespace App\Repository;

use App\Dto\UserDto;
use App\Models\Mongo\User as MongoUser;

class UserRepository
{
    public function findByChatIdOrCreate(UserDto $userDto): MongoUser
    {
        return MongoUser::query()->firstOrCreate(['chat_id' => $userDto->chatId], [
            'telegram_id' => $userDto->telegramId,
            'chat_id' => $userDto->chatId,
            'first_name' => $userDto->firstName,
            'last_name' => $userDto->lastName,
            'username' => $userDto->username,
            'language_code' => $userDto->languageCode,
        ]);
    }

    public function findById(string $userId): MongoUser
    {
        return MongoUser::query()->findOrFail($userId);
    }
}
