<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Managers\Telegram\QuestionsRedisManager;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\Entities\InlineKeyboard;
use App\Enums\Telegram\CourseTypeEnum;
use App\Enums\Workspace\CourseTypeEnum as WorkspaceCourseTypeEnum;

class CourseTypeStateHandler
{
    public function __construct(private readonly QuestionsRedisManager $questionsRedisManager) {}

    public function sendCourseTypeQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
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
                'chat_id' => $messageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __('bot_messages.course_type'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    public function acceptCourseTypeAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();

        if (in_array($messageDto->callbackData, array_column(WorkspaceCourseTypeEnum::cases(), 'value'))) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, CourseTypeEnum::QUESTION->value, $messageDto->callbackData, 1);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => 'Form of study process was sucessufylly save✅',
            ]);

            return true;
        }

        return false;
    }
}
