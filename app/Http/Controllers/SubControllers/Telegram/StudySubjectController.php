<?php

namespace App\Http\Controllers\SubControllers\Telegram;

use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Http\Requests\TelegramMessageRequest;
use App\Managers\Telegram\QuestionsRedisManager;
use Illuminate\Http\Response;
use App\Helpers\TelegramHelper;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Post;

class StudySubjectController
{
    #[Post('/subject/send-question')]
    public function sendSubjectQuestion(TelegramMessageRequest $request, QuestionsRedisManager $questionsRedisManager): Response
    {
        $data = $request->validated();
        $userId = $data['user']['_id'];

        if ($data['callbackData'] === $userId . '_' . SubjectStudiesEnum::QUESTION->value) {

            $questionsRedisManager->setAnswerForQuestion($userId, SubjectStudiesEnum::QUESTION->value);

            TelegramBotRequest::sendMessage([
                'chat_id' => $data['user']['chat_id'],
                'text' => __('bot_messages.subject_of_studies'),
                'parse_mode' => 'Markdown'
            ]);

            return response()->noContent();
        }

        return response()->noContent();
    }

    #[Post('/subject/accept-answer')]
    public function acceptSubjectAnswer(TelegramMessageRequest $request, QuestionsRedisManager $questionsRedisManager): JsonResponse
    {
        $data = $request->validated();
        $userId = $data['user']['_id'];

        if (TelegramHelper::notEmptyNotApprovedMessage($data, SubjectStudiesEnum::QUESTION->value)) {

            $questionsRedisManager->setAnswerForQuestion($userId, SubjectStudiesEnum::QUESTION->value, $data['answer'], 1);

            TelegramBotRequest::sendMessage([
                'chat_id' => $data['user']['chat_id'],
                'text' => 'Your title of object studies was save✅'
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }
}
