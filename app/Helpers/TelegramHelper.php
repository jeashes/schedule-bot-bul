<?php

namespace App\Helpers;

use App\Dto\TelegramMessageDto;

use Illuminate\Support\Facades\Redis;

class TelegramHelper
{
    static public function notEmptyNotApprovedMessage(TelegramMessageDto $messageDto, string $question): bool
    {
        $info = json_decode(Redis::get($messageDto->user->getId() . "_$question"), true);
        return !empty($messageDto->answer) && $info['approved'] === 0;
    }
}
