<?php

namespace App\Http\Controllers\SubControllers\Telegram;

use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Http\Requests\TelegramMessageRequest;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Enums\Workspace\ScheduleEnum as WorkspaceSchedule;
use Illuminate\Http\Response;
use App\Enums\Telegram\ScheduleEnum;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class StudyScheduleController
{
    #[Route('POST', '/schedule/send-question')]
    public function sendScheduleQuestion(TelegramMessageRequest $request, QuestionsRedisManager $questionsRedisManager): Response
    {
        $data = $request->validated();
        $userId = $data['user']['_id'];
        $scheduleInfo = json_decode(Redis::get($userId . '_' . ScheduleEnum::QUESTION->value), true);

        if (is_null($scheduleInfo['current_answer'])) {

            $questionsRedisManager->setAnswerForQuestion($userId, ScheduleEnum::QUESTION->value);

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
                'chat_id' => $data['user']['chat_id'],
                'reply_markup' => $keyboard,
                'text' => __('bot_messages.schedule'),
                'parse_mode' => 'Markdown'
            ]);

            return response()->noContent();
        }

        return response()->noContent();
    }

    #[Route('POST', '/schedule/accept-answer')]
    public function acceptScheduleAnswer(TelegramMessageRequest $request, QuestionsRedisManager $questionsRedisManager): JsonResponse
    {
        $data = $request->validated();
        $userId = $data['user']['_id'];

        if (in_array($data['callbackData'], array_column(WorkspaceSchedule::cases(), 'value'))) {

            $questionsRedisManager->setAnswerForQuestion($userId, ScheduleEnum::QUESTION->value, $data['callbackData'], 1);

            TelegramBotRequest::sendMessage([
                'chat_id' => $data['user']['chat_id'],
                'text' => 'Schedule was sucessufylly save✅',
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }
}
