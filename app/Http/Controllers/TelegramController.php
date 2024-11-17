<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\Telegram;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request): void
    {
        Log::channel('telegram')->debug(json_encode($request->all()));
        $botApiKey = config('telegram.bot_api_token');
        $botUserName = config('telegram.bot_username');
        try {
            $telegram = new Telegram($botApiKey, $botUserName);
            $telegram->handle();
            TelegramBotRequest::sendMessage([
                'chat_id' => $request->all()['message']['chat']['id'],
                'text' => 'Hello, World!'
            ]);
        } catch (TelegramException $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }
}
