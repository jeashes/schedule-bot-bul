<?php

namespace App\Http\Controllers\SubControllers\Telegram;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\SubjectStudiesEnum;
use Illuminate\Http\Client\Response;
use App\Helpers\TelegramHelper;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class StudySubjectController
{
    #[Route('POST', '/subject/send-question')]
    public function sendSubjectQuestion(TelegramMessageDto $messageDto): Response
    {
        if ($messageDto->callbackData === $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value) {

            Redis::set(
                $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value,
                json_encode(['current_answer' => '', 'approved' => 0])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.subject_of_studies'),
                'parse_mode' => 'Markdown'
            ]);

            return response()->noContent();
        }

        return response()->noContent();
    }

    #[Route('POST', '/subject/accept-answer')]
    public function acceptSubjectAnswer(TelegramMessageDto $messageDto): JsonResponse
    {
        $userId = $messageDto->user->getId();

        if (TelegramHelper::notEmptyNotApprovedMessage($messageDto, SubjectStudiesEnum::QUESTION->value)) {

            Redis::set(
                $userId . '_' . SubjectStudiesEnum::QUESTION->value,
                json_encode(['current_answer' => $messageDto->answer, 'approved' => 1])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Your title of object studies was save✅'
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }
}
