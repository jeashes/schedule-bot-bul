<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\CourseTypeEnum;
use App\Enums\Telegram\ToolsEnum;
use App\Enums\Workspace\CourseTypeEnum as WorkspaceCourseTypeEnum;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Managers\Telegram\QuestionsRedisManager;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;

class CourseTypeStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly HoursStateHandler $nextHandler,
        private readonly QuestionsRedisManager $questionsRedisManager
    ) {}

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        $userId = $messageDto->user->_id;
        $previousAnswer = $this->questionsRedisManager->getPreviousAnswer($userId, ToolsEnum::QUESTION->value);

        if ($chatState === ChatStateEnum::COURSE_TYPE->value && $previousAnswer) {
            $this->sendQuestion($messageDto);
            Log::channel('telegram')->info('Current course state: ' . $chatState);
            if ($this->acceptAnswer($messageDto)) {
                $messageDto->answer = null;
                $messageDto->callbackData = null;
                $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::HOURS->value);

                $this->nextHandler->handle($messageDto, ChatStateEnum::HOURS->value);
            }
        } else {
            Log::channel('telegram')->info('Go course hours state: ' . $chatState);
            $this->nextHandler->handle($messageDto, $chatState);
        }
    }

    private function sendQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->_id;
        $courseTypeInfo = json_decode(Redis::get($userId.'_'.CourseTypeEnum::QUESTION->value), true);

        if (is_null($courseTypeInfo['current_answer'])) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, CourseTypeEnum::QUESTION->value);

            $keyboard = new InlineKeyboard(
                [
                    [
                        'text' => 'Course',
                        'callback_data' => WorkspaceCourseTypeEnum::DEFAULT_COURSE->value,
                    ],
                    [
                        'text' => 'Tutorial',
                        'callback_data' => WorkspaceCourseTypeEnum::TUTORIAL->value,
                    ],
                    [
                        'text' => 'Pet Project',
                        'callback_data' => WorkspaceCourseTypeEnum::PET_PROJECT->value,
                    ],
                ]);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->chat_id,
                'reply_markup' => $keyboard,
                'text' => __('bot_messages.course_type'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
    {
        if (empty($messageDto->callbackData)) {
            return false;
        }

        $userId = $messageDto->user->_id;

        if (in_array($messageDto->callbackData, array_column(WorkspaceCourseTypeEnum::cases(), 'value'))) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, CourseTypeEnum::QUESTION->value, $messageDto->callbackData, 1);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->chat_id,
                'text' => 'Form of study process was sucessufylly save✅',
            ]);

            return true;
        }

        return false;
    }
}
