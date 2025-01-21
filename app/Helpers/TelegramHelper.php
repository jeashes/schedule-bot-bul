<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;

class TelegramHelper
{
    static public function notEmptyNotApprovedMessage(array $data, string $question): bool
    {
        $info = json_decode(Redis::get($data['user']['_id'] . "_$question"), true);
        return !empty($data['answer']) && $info['approved'] === 0;
    }
}
