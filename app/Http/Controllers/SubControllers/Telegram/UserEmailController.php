<?php

namespace App\Http\Controllers\SubControllers\Telegram;

use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\UserEmailEnum;
use App\Http\Requests\TelegramMessageRequest;
use Illuminate\Http\JsonResponse;
use App\Managers\Telegram\QuestionsRedisManager;
use Spatie\RouteAttributes\Attributes\Post;
use Illuminate\Http\Response;

class UserEmailController
{
    #[Post('/email/send-question')]
    public function sendEmailQuestion(TelegramMessageRequest $request, QuestionsRedisManager $questionsRedisManager): Response
    {
        $data = $request->validated();
        $userId = $data['user']['_id'];
        $userEmailInfo = json_decode(Redis::get($userId . '_' . UserEmailEnum::QUESTION->value), true);

        if (is_null($userEmailInfo['current_answer'])) {

            $questionsRedisManager->setAnswerForQuestion($userId, UserEmailEnum::QUESTION->value);

            TelegramBotRequest::sendMessage([
                'chat_id' => $data['user']['data'],
                'text' => __(
                    'bot_messages.ask_email',
                    ['triesCount' => 3]
                ),
                'parse_mode' => 'Markdown'
            ]);

            return response()->noContent();
        }

        return response()->noContent();
    }

    #[Post('/email/accept-answer')]
    public function acceptEmailAnswer(TelegramMessageRequest $request, QuestionsRedisManager $questionsRedisManager): JsonResponse
    {
        $data = $request->validated();
        $userId = $data['user']['_id'];
        $validatedEmail = $this->validateEmail($data['answer']);

        switch ($validatedEmail) {
            case true:
                $questionsRedisManager->setAnswerForQuestion($userId, UserEmailEnum::QUESTION->value, $data['answer'], 1);

                return response()->json(['success' => $validatedEmail]);
            case false:
                TelegramBotRequest::sendMessage([
                    'chat_id' => $data['user']['chat_id'],
                    'text' => __(
                        'bot_messages.wrong_email',
                        ['email' => $data['answer']]
                    ),
                ]);

                return response()->json(['success' => $validatedEmail]);
        }

        return response()->json(['success' => false]);
    }

    private function validateEmail(?string $email): bool
    {
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
        return (preg_match($pattern, $email ?? '')) ? true : false;
    }
}
