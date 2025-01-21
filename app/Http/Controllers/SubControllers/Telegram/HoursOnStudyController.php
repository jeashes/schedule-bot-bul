<?php

namespace App\Http\Controllers\SubControllers\Telegram;

use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Http\Requests\TelegramMessageRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HourOnStudyController
{
    #[Route('POST', '/hours/send-question')]
    public function sendHoursQuestion(TelegramMessageRequest $request, QuestionsRedisManager $questionsRedisManager): Response
    {
        $data = $request->validated();
        $userId = $data['user']['_id'];

        $hoursOnStudyInfo = json_decode(Redis::get($userId . '_' . HoursOnStudyEnum::QUESTION->value), true);

        if (is_null($hoursOnStudyInfo['current_answer'])) {

            $questionsRedisManager->setAnswerForQuestion($userId, HoursOnStudyEnum::QUESTION->value, '', 0);

            TelegramBotRequest::sendMessage([
                'chat_id' => $data['user']['chat_id'],
                'text' => __('bot_messages.total_hours_on_study')
            ]);

            return response()->noContent();
        }

        return response()->noContent();
    }

    #[Route('POST', '/hours/accept-answer')]
    public function acceptHoursAnswer(TelegramMessageRequest $request, QuestionsRedisManager $questionsRedisManager): JsonResponse
    {
        $data = $request->validated();
        $userId = $data['user']['id'];

        $validateHours = $this->validateHours($data['answer']);

        switch ($validateHours) {
            case true:

                $questionsRedisManager->setAnswerForQuestion($userId, HoursOnStudyEnum::QUESTION->value, $data['answer'], 1);

                TelegramBotRequest::sendMessage([
                    'chat_id' => $userId,
                    'text' => 'Hours on studying was sucessufully save✅'
                ]);

                return response()->json(['success' => $validateHours]);
            case false:
                TelegramBotRequest::sendMessage([
                    'chat_id' => $data['user']['chat_id'],
                    'text' => __(
                        'bot_messages.wrong_hours',
                        ['hours' => $data['answer']]
                    ),
                ]);
                return response()->json(['success' => $validateHours]);
        }

        return response()->json(['success' => false]);
    }

    private function validateHours(?string $hours): bool
    {
        $pattern = "/^(0|[1-9]\d*)(\.\d+)?$/";
        return preg_match($pattern, $hours ?? '') ? true: false;
    }
}
