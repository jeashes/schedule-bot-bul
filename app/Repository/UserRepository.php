<?php

namespace App\Repository;

use App\Dto\UserDto;
use App\Models\Mongo\User as MongoUser;

class UserRepository
{
    public function findByChatIdOrCreate(UserDto $userDto): MongoUser
    {
        $userMessageData = [
            'first_name' => $userDto->getFirstName(),
            'chat_id' => $userDto->getChatId(),
            'language_code' => $userDto->getLanguageCode()
        ];

        if (!is_null($userDto->getLastName())) {
            $userMessageData['last_name'] = $userDto->getLastName();
        }

        if (!is_null($userDto->getUsername())) {
            $userMessageData['username'] = $userDto->getUsername();
        }

        return MongoUser::firstOrCreate(['chat_id' => $userDto->getChatId()], $userMessageData);
    }
}
