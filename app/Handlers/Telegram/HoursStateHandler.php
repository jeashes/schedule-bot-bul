<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Managers\Telegram\QuestionsRedisManager;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Interfaces\Telegram\StateHandlerInterface;

class HoursStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly ScheduleStateHandler $nextHandler,
        private readonly QuestionsRedisManager $questionsRedisManager
    ) {}

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        if ($chatState === ChatStateEnum::HOURS->value) {
            $this->sendQuestion($messageDto);
            if ($this->acceptAnswer($messageDto)) {
                $this->questionsRedisManager->updateChatState($messageDto->user->getId(), ChatStateEnum::SCHEDULE->value);

                $this->nextHandler->handle($messageDto, ChatStateEnum::SCHEDULE->value);
            } else {
                $this->nextHandler->handle($messageDto, ChatStateEnum::SCHEDULE->value);
            }
        }
    }

    private function sendQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();

        $hoursOnStudyInfo = json_decode(Redis::get($userId.'_'.HoursOnStudyEnum::QUESTION->value), true);

        if (is_null($hoursOnStudyInfo['current_answer'])) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, HoursOnStudyEnum::QUESTION->value);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.total_hours_on_study'),
            ]);
        }
    }

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();

        $validateHours = $this->validateHours($messageDto->answer);

        switch ($validateHours) {
            case true:

                $this->questionsRedisManager->setAnswerForQuestion($userId, HoursOnStudyEnum::QUESTION->value, $messageDto->answer, 1);

                TelegramBotRequest::sendMessage([
                    'chat_id' => $userId,
                    'text' => 'Hours on studying was sucessufully save✅',
                ]);

                return $validateHours;
            case false:
                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __(
                        'bot_messages.wrong_hours',
                        ['hours' => $messageDto->answer]
                    ),
                ]);

                return $validateHours;
        }

        return false;
    }

    private function validateHours(?string $hours): bool
    {
        $pattern = "/^(0|[1-9]\d*)(\.\d+)?$/";

        return preg_match($pattern, $hours ?? '') ? true : false;
    }
}
