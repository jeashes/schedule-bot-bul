<?php

namespace App\Http\Controllers;

use App\Dto\TelegramMessageDto;
use App\Dto\UserDto;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Exceptions\TelegramIdNotFoundException;
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
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

class TelegramController extends Controller
{
    public function __construct(
        private readonly Telegram $telegram,
        private readonly MessageHandler $messageHandler,
        private readonly QuestionsRedisManager $questionsRedisManager,
    ) {

    }

    #[Post('/webhook/{tg_secret}')]
    #[Middleware('verify.telegram')]
    public function handleWebhook(Request $request, UserRepository $userRepository): void
    {
        Log::channel('telegram')->debug('Telegram request data', $request->all());

        try {
            $this->telegram->handle();

            $requestData = $request->toArray();

            $messageFrom = data_get($requestData, 'message.from')
                ?? data_get($requestData, 'callback_query.from')
                ?? data_get($requestData, 'inline_query.from');

            $chatId = data_get($requestData, 'message.chat.id')
                ?? data_get($requestData, 'my_chat_member.chat.id')
                ?? data_get($requestData, 'callback_query.message.chat.id');

            $telegramId = $messageFrom['id'];

            if (! $telegramId) {
                Log::channel('telegram')->error('Telegram id not found', $requestData);
                throw new TelegramIdNotFoundException('Telegram id not found. Please check telegram logs and message structure');
            }

            if (! $chatId) {
                Log::channel('telegram')->debug('Chat id not found', $requestData);
                $chatId = $telegramId;
            }

            $userDto = new UserDto(
                username: $messageFrom['username'] ?? null,
                firstName: $messageFrom['first_name'] ?? '#####',
                lastName: $messageFrom['last_name'] ?? null,
                chatId: $chatId,
                languageCode: $messageFrom['language_code'] ?? 'en',
                telegramId: $telegramId
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
            Log::channel('telegram')->error($e->getMessage().' '.json_encode($request), [
                'request' => json_encode($request),
            ]);
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
