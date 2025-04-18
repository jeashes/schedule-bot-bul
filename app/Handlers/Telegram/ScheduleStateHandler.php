<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\ScheduleEnum;
use App\Enums\Workspace\ScheduleEnum as WorkspaceSchedule;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Managers\Telegram\QuestionsRedisManager;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Request as TelegramBotRequest;

class ScheduleStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly EmailStateHandler $nextHandler,
        private readonly QuestionsRedisManager $questionsRedisManager
    ) {}

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        $userId = $messageDto->user->getId();
        $previousAnswer = $this->questionsRedisManager->getPreviousAnswer($userId, HoursOnStudyEnum::QUESTION->value);

        if ($chatState === ChatStateEnum::SCHEDULE->value && $previousAnswer) {
            $this->sendQuestion($messageDto);
            Log::channel('telegram')->info('Current schedule state: ' . $chatState);
            if ($this->acceptAnswer($messageDto)) {
                $messageDto->answer = null;
                $messageDto->callbackData = null;
                $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::EMAIL->value);

                $this->nextHandler->handle($messageDto, ChatStateEnum::EMAIL->value);
            }
        } else {
            Log::channel('telegram')->info('Go to email handler: ' . $chatState);
            $this->nextHandler->handle($messageDto, $chatState);
        }
    }

    private function sendQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
        $scheduleInfo = json_decode(Redis::get($userId.'_'.ScheduleEnum::QUESTION->value), true);

        if (is_null($scheduleInfo['current_answer'])) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, ScheduleEnum::QUESTION->value);

            $keyboard = new InlineKeyboard(
                [
                    [
                        'text' => 'Mon-Wed-Fri',
                        'callback_data' => WorkspaceSchedule::MON_WED_FRI->value,
                    ],
                    [
                        'text' => 'Tue-Thu-Sat',
                        'callback_data' => WorkspaceSchedule::TUE_THU_SAT->value,
                    ],
                ],
                [
                    [
                        'text' => 'Sat-Sun',
                        'callback_data' => WorkspaceSchedule::SAT_SUN->value,
                    ],
                    [
                        'text' => 'All Weekdays',
                        'callback_data' => WorkspaceSchedule::ALL_WEEKDAYS->value,
                    ],
                    [
                        'text' => 'EVERY DAY',
                        'callback_data' => WorkspaceSchedule::EVERY_DAY->value,
                    ],
                ]);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __('bot_messages.schedule'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
    {
        if (empty($messageDto->answer)) {
            return false;
        }
        
        $userId = $messageDto->user->getId();

        if (in_array($messageDto->callbackData, array_column(WorkspaceSchedule::cases(), 'value'))) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, ScheduleEnum::QUESTION->value, $messageDto->callbackData, 1);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Schedule was sucessufylly save✅',
            ]);

            return true;
        }

        return false;
    }
}
