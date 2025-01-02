<?php

namespace App\Traits\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\AnswerEditAcceptEnum;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\SubjectStudiesEnum;

trait StudySubject
{
    public function sendSubjectQuestion(TelegramMessageDto $messageDto): void
    {
        if ($messageDto->callbackData === $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value) {

            Redis::set(
                $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value,
                json_encode([
                    'current_answer' => '',
                    'edited' => 0,
                    'approved' => 0
                ])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __(
                    'bot_messages.subject_of_studies',
                    ['triesCount' => 3]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function clarifySubjectAnswer(TelegramMessageDto $messageDto): void
    {
        $subjectStudiesInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value), true);

        if (!empty($messageDto->answer)
            && $subjectStudiesInfo['edited'] < AnswerEditAcceptEnum::EDIT_COUNT_CLARIFY->value
            && $subjectStudiesInfo['approved'] === 0) {

            $keyboard = new InlineKeyboard([
                [
                    'text' => 'Yes',
                    'callback_data' => SubjectStudiesEnum::NAME_ACCEPT->value
                ]
            ]);

            Redis::set(
                $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $messageDto->answer,
                    'edited' => $subjectStudiesInfo['edited'] + 1,
                    'approved' => $subjectStudiesInfo['approved']
                ])
            );

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    'bot_messages.validate_answer',
                    ['title' => $messageDto->answer]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }

        if (!empty($messageDto->answer)
            && $subjectStudiesInfo['edited'] === AnswerEditAcceptEnum::EDIT_COUNT_CLARIFY->value
            && $subjectStudiesInfo['approved'] === 0) {

            Redis::set(
                $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $messageDto->answer,
                    'edited' => $subjectStudiesInfo['edited'] + 1,
                    'approved' => $subjectStudiesInfo['approved']
                ])
            );
        }
    }

    public function acceptSubjectAnswer(TelegramMessageDto $messageDto): void
    {
        $subjectStudiesInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value), true);

        if (($messageDto->callbackData === SubjectStudiesEnum::NAME_ACCEPT->value
            || $subjectStudiesInfo['edited'] >= AnswerEditAcceptEnum::EDIT_COUNT_ACCEPT->value
            && !empty($messageDto->answer))
            && $subjectStudiesInfo['approved'] === 0) {

            Redis::set(
                $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $subjectStudiesInfo['current_answer'],
                    'edited' => $subjectStudiesInfo['edited'],
                    'approved' => 1
                ])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Your title of object studies was save✅'
            ]);
        }
    }
}
