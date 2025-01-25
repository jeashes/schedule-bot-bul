<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Enums\Workspace\ScheduleEnum as WorkspaceSchedule;
use App\Enums\Telegram\ScheduleEnum;

class ScheduleStateHandler
{
    static public function sendScheduleQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
        $scheduleInfo = json_decode(Redis::get($userId . '_' . ScheduleEnum::QUESTION->value), true);

        if (is_null($scheduleInfo['current_answer'])) {

            QuestionsRedisManager::setAnswerForQuestion($userId, ScheduleEnum::QUESTION->value);

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
                'text' => __('bot_messages.schedule'),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    static public function acceptScheduleAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();

        if (in_array($messageDto->callbackData, array_column(WorkspaceSchedule::cases(), 'value'))) {

            QuestionsRedisManager::setAnswerForQuestion($userId, ScheduleEnum::QUESTION->value, $messageDto->callbackData, 1);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Schedule was sucessufylly save✅',
            ]);

            return true;
        }

        return false;
    }
}
