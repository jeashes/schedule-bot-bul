<?php

namespace App\Http\Controllers;

use App\Dto\TelegramMessageDto;
use App\Dto\UserDto;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Handlers\Telegram\MessageHandler;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Repository\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\Telegram;
use Spatie\RouteAttributes\Attributes\Post;

class TelegramController extends Controller
{
    public function __construct(
        private readonly Telegram $telegram,
        private readonly MessageHandler $messageHandler,
        private readonly QuestionsRedisManager $questionsRedisManager,
    ) {}

    #[Post('/webhook')]
    public function handleWebhook(Request $request, UserRepository $userRepository): void
    {
        Log::channel('telegram')->debug(json_encode($request->all()));

        try {
            $this->telegram->handle();

            $messageFrom = ($request['message']['from'] ?? $request['callback_query']['from'])
                    ?? $request['my_chat_member']['from'];

            $userDto = new UserDto(
                username: array_key_exists('username', $messageFrom) ? $messageFrom['username'] : null,
                firstName: $messageFrom['first_name'],
                lastName: array_key_exists('last_name', $messageFrom) ? $messageFrom['last_name'] : null,
                chatId: $messageFrom['id'],
                languageCode: $messageFrom['language_code'],
            );

            $user = $userRepository->findByChatIdOrCreate($userDto);
            $message = new TelegramMessageDto(
                $request['message']['text'] ?? null,
                $request['callback_query']['data'] ?? null,
                $user
            );

            $this->botStartMessage($message);

            $this->messageHandler->handleMessages($message);

        } catch (TelegramException $e) {
            Log::channel('telegram')->error($e->getMessage());
        }
    }

    private function botStartMessage(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
        if ($messageDto->answer === '/start') {

            $this->questionsRedisManager->resetUserAnswers($userId);
            $keyboard = new InlineKeyboard([
                [
                    'text' => 'LET\'S GOOO',
                    'callback_data' => $userId.'_'.SubjectStudiesEnum::QUESTION->value,
                ],
            ]);

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    'bot_messages.welcome', [
                        'name' => $messageDto->user->getFirstName().' '
                        .$messageDto->user->getLastName(),
                    ]
                ),
                'parse_mode' => 'Markdown',
            ]);
        }
    }
}
