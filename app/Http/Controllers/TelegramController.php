<?php

namespace App\Http\Controllers;

use App\Dto\TelegramMessageDto;
use App\Dto\UserDto;
use App\Managers\TelegramMessageManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Repository\UserRepository;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class TelegramController extends Controller
{
    public function __construct(
        private readonly Telegram $telegram,
        private readonly TelegramMessageManager $telegramMessageManager
    ) {

    }

    public function handleWebhook(Request $request, UserRepository $userRepository): void
    {
        Log::channel('telegram')->debug(json_encode($request->all()));

        try {
            $this->telegram->handle();

            $messageFrom = ($request['message']['from'] ?? $request['callback_query']['from'])
                    ?? $request['my_chat_member']['from'];

            $userDto = new UserDto(
                username: array_key_exists('username', $messageFrom) ? $messageFrom['username']: null,
                firstName: $messageFrom['first_name'],
                lastName: array_key_exists('last_name', $messageFrom) ? $messageFrom['last_name']: null,
                chatId: $messageFrom['id'],
                languageCode: $messageFrom['language_code'],
            );

            $user = $userRepository->findByChatIdOrCreate($userDto);
            $message = new TelegramMessageDto(
                $request['message']['text'] ?? null,
                $request['callback_query']['data'] ?? null,
                $user
            );

            $this->telegramMessageManager->handleMessages($message);

        } catch (TelegramException $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }
}
