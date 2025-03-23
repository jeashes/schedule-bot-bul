<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\ScheduleEnum;
use App\Enums\Workspace\ScheduleEnum as WorkspaceSchedule;
use App\Managers\Telegram\QuestionsRedisManager;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Interfaces\Telegram\StateHandlerInterface;

class ScheduleStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly EmailStateHandler $nextHandler,
        private readonly QuestionsRedisManager $questionsRedisManager
    ) {}

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        if ($chatState === ChatStateEnum::SCHEDULE->value) {
            $this->sendQuestion($messageDto);
            if ($this->acceptAnswer($messageDto)) {
                $this->questionsRedisManager->updateChatState($messageDto->user->getId(), ChatStateEnum::EMAIL->value);
                $this->nextHandler->handle($messageDto, ChatStateEnum::EMAIL->value);
            }
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
