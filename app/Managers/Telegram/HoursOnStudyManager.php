<?php

namespace App\Managers\Telegram;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Interfaces\TelegramMessageManagerInterface;
use Illuminate\Support\Facades\Log;

class HoursOnStudyManager implements TelegramMessageManagerInterface
{
    const EDIT_COUNT_CLARIFY = 2;
    const EDIT_COUNT_ACCEPT = 3;

    public function sendQuestion(TelegramMessageDto $messageDto): void
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

    public function clarifyAnswer(TelegramMessageDto $messageDto): void
    {
        $hoursOnStudyInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value), true);

        if (!empty($messageDto->answer)
            && $hoursOnStudyInfo['edited'] < self::EDIT_COUNT_CLARIFY
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
                    ['title' => $messageDto->answer]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }

        if (!empty($messageDto->answer)
            && $hoursOnStudyInfo['edited'] === self::EDIT_COUNT_CLARIFY && $hoursOnStudyInfo['approved'] === 0) {

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

    public function acceptAnswer(TelegramMessageDto $messageDto): void
    {
        $hoursOnStudyInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value), true);

        if (($messageDto->callbackData === HoursOnStudyEnum::NAME_ACCEPT->value
            || $hoursOnStudyInfo['edited'] >= self::EDIT_COUNT_ACCEPT && !empty($messageDto->answer))
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
