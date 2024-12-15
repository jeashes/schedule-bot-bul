<?php

namespace App\Managers;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\PaceLevelEnum;
use App\Enums\Workspace\PaceLevelEnum as WorkspacePaceLevelEnum;
use Illuminate\Support\Facades\Log;

class TelegramStudyPaceLevelManager
{
    public function sendQuestion(TelegramMessageDto $messageDto): void
    {
        $hoursOnStudyInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . PaceLevelEnum::QUESTION->value), true);
        if (is_null($hoursOnStudyInfo['current_answer'])) {
            Redis::set(
                $messageDto->user->getId() . '_' . PaceLevelEnum::QUESTION->value,
                json_encode([
                    'current_answer' => '',
                    'approved' => 0
                ])
            );

            $keyboard = new InlineKeyboard([
                [
                    'text' => 'EASY',
                    'callback_data' => WorkspacePaceLevelEnum::EASY->value
                ],
                [
                    'text' => 'MEDIUM',
                    'callback_data' => WorkspacePaceLevelEnum::MEDIUM->value
                ],
                [
                    'text' => 'HARD',
                    'callback_data' => WorkspacePaceLevelEnum::HARD->value
                ],
            ]);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    'bot_messages.pace_level',
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function acceptAnswer(TelegramMessageDto $messageDto): void
    {
        Log::channel('telegram')->debug('Callback data for pace level' . $messageDto->callbackData);
        if (in_array(
            $messageDto->callbackData,
            array_column(WorkspacePaceLevelEnum::cases(), 'value')
        )) {
            Redis::set(
                $messageDto->user->getId() . '_' . PaceLevelEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $messageDto->callbackData,
                    'approved' => 1
                ])
            );

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Pace level was sucessufylly save✅',
            ]);
        }
    }
}
