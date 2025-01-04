<?php

namespace App\Traits\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Workspace\ScheduleEnum as WorkspaceSchedule;
use App\Enums\Telegram\ScheduleEnum;
use Illuminate\Support\Facades\Log;

trait StudySchedule
{
    public function sendScheduleQuestion(TelegramMessageDto $messageDto): void
    {
        $scheduleInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . ScheduleEnum::QUESTION->value), true);
        if (is_null($scheduleInfo['current_answer'])) {
            Redis::set(
                $messageDto->user->getId() . '_' . ScheduleEnum::QUESTION->value,
                json_encode([
                    'current_answer' => '',
                    'approved' => 0
                ])
            );

            $keyboard = new InlineKeyboard(
            [
                [
                    'text' => 'Mon-Wed-Fri',
                    'callback_data' => WorkspaceSchedule::MON_WED_FRI->value
                ],
                [
                    'text' => 'Tue-Thu-Sat',
                    'callback_data' => WorkspaceSchedule::TUE_THU_SAT->value
                ],
            ],
            [
                [
                    'text' => 'Sat-Sun',
                    'callback_data' => WorkspaceSchedule::SAT_SUN->value
                ],
                [
                    'text' => 'All Weekdays',
                    'callback_data' => WorkspaceSchedule::ALL_WEEKDAYS->value
                ],
                [
                    'text' => 'EVERY DAY',
                    'callback_data' => WorkspaceSchedule::EVERY_DAY->value
                ],
            ]);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    'bot_messages.schedule',
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function acceptScheduleAnswer(TelegramMessageDto $messageDto): void
    {
        Log::channel('telegram')->info('shedule answer accept' . $messageDto->callbackData);
        if (in_array(
            $messageDto->callbackData,
            array_column(WorkspaceSchedule::cases(), 'value')
        )) {
            Redis::set(
                $messageDto->user->getId() . '_' . ScheduleEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $messageDto->callbackData,
                    'approved' => 1
                ])
            );

            $messageDto->callbackData = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Schedule was sucessufylly save✅',
            ]);
        }
    }
}
