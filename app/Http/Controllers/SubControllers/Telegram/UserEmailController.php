<?php

namespace App\Http\Controllers\SubControllers\Telegram;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\UserEmailEnum;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class UserEmailController
{
    #[Route('POST', '/email/send-question')]
    public function sendEmailQuestion(TelegramMessageDto $messageDto): Response
    {
        $userEmailInfo = json_decode(Redis::get($messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value), true);

        if (is_null($userEmailInfo['current_answer'])) {
            Redis::set(
                $messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value,
                json_encode(['current_answer' => '', 'approved' => 0])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
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

    #[Route('POST', '/email/accept-answer')]
    public function acceptEmailAnswer(TelegramMessageDto $messageDto): JsonResponse
    {
        $userId = $messageDto->user->getId();
        $validatedEmail = $this->validateEmail($messageDto->answer);

        switch ($validatedEmail) {
            case true:
                Redis::set(
                    $userId . '_' . UserEmailEnum::QUESTION->value,
                    json_encode(['current_answer' => $messageDto->answer, 'approved' => 1])
                );

                $messageDto->answer = null;
                return response()->json(['success' => $validatedEmail]);
            case false:
                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __(
                        'bot_messages.wrong_email',
                        ['email' => $messageDto->answer]
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
