<?php

namespace App\Http\Controllers;

use App\Dto\TelegramMessageDto;
use App\Dto\UserDto;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Exceptions\ChatIdNotFoundException;
use App\Handlers\Telegram\MessageHandler;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Models\Mongo\BotPhrases;
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
    ) {
    }

    #[Post('/webhook')]
    public function handleWebhook(Request $request, UserRepository $userRepository): void
    {
        Log::channel('telegram')->debug(json_encode($request->all()));

        try {
            $this->telegram->handle();

            $requestData = $request->toArray();

            $messageFrom = $this->getMessageFrom($requestData, ['my_chat_member', 'chat'])
            ?? $this->getMessageFrom($requestData, ['my_chat_member', 'from'])
            ?? $this->getMessageFrom($requestData, ['message', 'from'])
            ?? $this->getMessageFrom($requestData, ['callback_query', 'from']);

            if (! $messageFrom) {
                throw new ChatIdNotFoundException('Chat id not foundm check telegram logs and message structure');
            }

            $userDto = new UserDto(
                username: array_key_exists('username', $messageFrom) ? $messageFrom['username'] : null,
                firstName: $messageFrom['first_name'] ?? '#####',
                lastName: array_key_exists('last_name', $messageFrom) ? $messageFrom['last_name'] : null,
                chatId: $messageFrom['id'],
                languageCode: $messageFrom['language_code'] ?? 'en',
            );

            $user = $userRepository->findByChatIdOrCreate($userDto);
            $languageCode = ($request['message']['from']['language_code'] ?? $request['callback_query']['from']['language_code']) ?? BotPhrases::DEFAULT_LANGUAGE_CODE;

            $message = new TelegramMessageDto(
                $request['message']['text'] ?? null,
                $request['callback_query']['data'] ?? null,
                $user,
                $languageCode
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
            $this->questionsRedisManager->uploadBotPhrasesByLang($messageDto->languageCode);
    
            $keyboard = new InlineKeyboard([
                [
                    'text' => $this->questionsRedisManager->getBotPhraseByKey($messageDto->languageCode, 'lets_go'),
                    'callback_data' => $userId.'_'.SubjectStudiesEnum::QUESTION->value,
                ],
            ]);

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    $this->questionsRedisManager->getBotPhraseByKey($messageDto->languageCode, 'welcome'),
                    [
                        'name' => $messageDto->user->getFirstName().' '
                        .$messageDto->user->getLastName(),
                    ]
                ),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function getMessageFrom(array $data, array $keys): ?array
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return null;
            }
        }

        return $data;
    }
}
