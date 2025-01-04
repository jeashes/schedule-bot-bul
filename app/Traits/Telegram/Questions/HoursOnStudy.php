<?php

namespace App\Traits\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\AnswerEditAcceptEnum;

trait HoursOnStudy
{
    public function sendHoursQuestion(TelegramMessageDto $messageDto): void
    {
        $hoursOnStudyInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value), true);

        if (is_null($hoursOnStudyInfo['current_answer'])) {
            Redis::set(
                $messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value,
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
                    'bot_messages.total_hours_on_study',
                    ['triesCount' => 3]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function clarifyHoursAnswer(TelegramMessageDto $messageDto): void
    {
        $hoursOnStudyInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value), true);

        if (!empty($messageDto->answer)
            && $hoursOnStudyInfo['edited'] < AnswerEditAcceptEnum::EDIT_COUNT_CLARIFY->value
            && $hoursOnStudyInfo['approved'] === 0) {

            $keyboard = new InlineKeyboard([
                [
                    'text' => 'Yes',
                    'callback_data' => HoursOnStudyEnum::NAME_ACCEPT->value
                ]
            ]);

            Redis::set(
                $messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $messageDto->answer,
                    'edited' => $hoursOnStudyInfo['edited'] + 1,
                    'approved' => $hoursOnStudyInfo['approved']
                ])
            );

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    'bot_messages.validate_answer',
                    ['title' => $messageDto->answer . 'h']
                ),
                'parse_mode' => 'Markdown'
            ]);
        }

        if (!empty($messageDto->answer)
            && $hoursOnStudyInfo['edited'] === AnswerEditAcceptEnum::EDIT_COUNT_CLARIFY->value
            && $hoursOnStudyInfo['approved'] === 0) {

            Redis::set(
                $messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $messageDto->answer,
                    'edited' => $hoursOnStudyInfo['edited'] + 1,
                    'approved' => $hoursOnStudyInfo['approved']
                ])
            );
        }
    }

    public function acceptHoursAnswer(TelegramMessageDto $messageDto): void
    {
        $hoursOnStudyInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value), true);

        if (($messageDto->callbackData === HoursOnStudyEnum::NAME_ACCEPT->value
            || $hoursOnStudyInfo['edited'] >= AnswerEditAcceptEnum::EDIT_COUNT_ACCEPT->value
            && !empty($messageDto->answer))
            && $hoursOnStudyInfo['approved'] === 0) {

            Redis::set(
                $messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $hoursOnStudyInfo['current_answer'],
                    'edited' => $hoursOnStudyInfo['edited'],
                    'approved' => 1
                ])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Hours on studying was sucessufully save✅'
            ]);
        }
    }
}
