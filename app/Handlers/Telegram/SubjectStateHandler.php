<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Helpers\TelegramHelper;

class SubjectStateHandler
{
    static public function sendSubjectQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();

        if ($messageDto->callbackData === $userId . '_' . SubjectStudiesEnum::QUESTION->value) {

            QuestionsRedisManager::setAnswerForQuestion($userId, SubjectStudiesEnum::QUESTION->value);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.subject_of_studies'),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    static public function acceptSubjectAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();

        if (TelegramHelper::notEmptyNotApprovedMessage($messageDto, SubjectStudiesEnum::QUESTION->value)) {

            QuestionsRedisManager::setAnswerForQuestion($userId, SubjectStudiesEnum::QUESTION->value, $messageDto->answer, 1);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Your title of object studies was save✅'
            ]);

            return true;
        }

        return false;
    }
}
