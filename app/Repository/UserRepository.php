<?php

namespace App\Repository;

use App\Dto\UserDto;
use App\Models\Mongo\User as MongoUser;

class UserRepository
{
    public function findByChatIdOrCreate(UserDto $userDto): MongoUser
    {
        $userMessageData = [
            'first_name' => $userDto->firstName,
            'chat_id' => $userDto->chatId,
            'language_code' => $userDto->languageCode
        ];

        if (!is_null($userDto->lastName)) {
            $userMessageData['last_name'] = $userDto->lastName;
        }

        if (!is_null($userDto->username)) {
            $userMessageData['username'] = $userDto->username;
        }

        return MongoUser::query()->firstOrCreate(['chat_id' => $userDto->chatId], $userMessageData);
    }

    public function findById(string $userId): MongoUser
    {
        return MongoUser::query()->findOrFail($userId);
    }
}
