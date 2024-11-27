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
    public function __construct(private readonly Telegram $telegram)
    {

    }

    public function handleWebhook(Request $request, UserRepository $userRepository): void
    {
        Log::channel('telegram')->debug(json_encode($request->all()));

        try {
            $this->telegram->handle();

            $messageFrom = $request['message']['from'] ?? $request['callback_query']['from'];

            $userDto = new UserDto(
                username: array_key_exists('username', $messageFrom) ? $messageFrom['username']: null,
                firstName: $messageFrom['first_name'],
                lastName: array_key_exists('last_name', $messageFrom) ? $messageFrom['last_name']: null,
                chatId: $messageFrom['id'],
                languageCode: $messageFrom['language_code'],
            );

            $user = $userRepository->findByChatIdOrCreate($userDto);

            $this->handleBotStart($user, $request['message']['text'] ?? '');
            $this->handleClickLetsGoButton($user, $request['callback_query']['data'] ?? '');

        } catch (TelegramException $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    private function handleBotStart(MongoUser $user, string $text): void
    {
        if ($text === '/start') {
            $keyboard = new InlineKeyboard([
                [
                    'text' => 'LET\'S GOOO',
                    'callback_data' => 'start_questions'
                ]
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

    private function handleClickLetsGoButton(MongoUser $user, string $callbackData): void
    {
        if ($callbackData === 'start_questions') {
            TelegramBotRequest::sendMessage([
                'chat_id' => $user->getChatId(),
                'text' => 'Test button handling'
            ]);
        }
    }
}
