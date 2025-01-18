<?php

namespace App\Traits\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Helpers\TelegramHelper;

trait StudySubject
{
    public function sendSubjectQuestion(TelegramMessageDto $messageDto): void
    {
        if ($messageDto->callbackData === $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value) {

            Redis::set(
                $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value,
                json_encode([ 'current_answer' => '', 'approved' => 0])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.subject_of_studies'),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function acceptSubjectAnswer(TelegramMessageDto $messageDto): void
    {
        if (TelegramHelper::notEmptyNotApprovedMessage($messageDto, SubjectStudiesEnum::QUESTION->value)) {

            Redis::set(
                $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value,
                json_encode(['current_answer' => $messageDto->answer, 'approved' => 1])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Your title of object studies was save✅'
            ]);
        }
    }
}
