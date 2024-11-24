<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Mongo\User as MongoUser;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\Telegram;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request): void
    {
        $botApiKey = config('telegram.bot_api_token');
        $botUserName = config('telegram.bot_username');
        Log::channel('telegram')->debug(json_encode($request->all()));

        try {
            $telegram = new Telegram($botApiKey, $botUserName);
            $telegram->handle();

            $user = MongoUser::firstOrCreate(
                ['chat_id' => $request->all()['message']['chat']['id']],
                [
                    'username' => $request->all()['message']['from']['username'],
                    'first_name' => $request->all()['message']['from']['first_name'],
                    'last_name' => $request->all()['message']['from']['last_name'],
                    'chat_id' => $request->all()['message']['chat']['id'],
                    'language_code' => $request->all()['message']['from']['language_code']
                ]
            );

            TelegramBotRequest::sendMessage([
                'chat_id' => $user->getChatId(),
                'text' => 'Hello, ' . $user->getUserName()
            ]);
        } catch (TelegramException $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }
}
