<?php

namespace App\Http\Controllers;

use App\Dto\UserDto;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Mongo\User as MongoUser;
use App\Repository\UserRepository;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\Telegram;

class TelegramController extends Controller
{
    public function handleWebhook(Request $request, UserRepository $userRepository): void
    {
        $botApiKey = config('telegram.bot_api_token');
        $botUserName = config('telegram.bot_username');

        try {
            $telegram = new Telegram($botApiKey, $botUserName);
            $telegram->handle();

            $messageFrom = $request['message']['from'];

            $userDto = new UserDto(
                username: array_key_exists('username', $messageFrom) ? $messageFrom['username']: null,
                firstName: $messageFrom['first_name'],
                lastName: array_key_exists('last_name', $messageFrom) ? $messageFrom['last_name']: null,
                chatId: $messageFrom['id'],
                languageCode: $messageFrom['language_code'],
            );

            $user = $userRepository->findByChatIdOrCreate($userDto);

            $this->handleBotStart($user, $request['message']['text']);

        } catch (TelegramException $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    private function handleBotStart(MongoUser $user, string $text): void
    {
        if ($text === '/start') {
            $keyboard = new InlineKeyboard([
                ['text' => 'LET\'S GOOO', 'url' => 'https://www.youtube.com/watch?v=44pt8w67S8I']
            ]);

            TelegramBotRequest::sendMessage([
                'chat_id' => $user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    'bot_messages.welcome', ['name' => $user->getFirstName() . ' ' . $user->getLastName()]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }
}
