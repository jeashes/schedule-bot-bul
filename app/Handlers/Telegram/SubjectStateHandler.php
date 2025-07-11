<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Service\OpenAi\SubjectValidator;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Request as TelegramBotRequest;

class SubjectStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly GoalStateHandler $nextHandler,
        private readonly QuestionsRedisManager $questionsRedisManager,
        private readonly SubjectValidator $subjectValidator,
    ) {}

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        if ($chatState === ChatStateEnum::SUBJECT_STUDY->value) {
            $this->sendQuestion($messageDto);
            Log::channel('telegram')->info('Current subject state: ' . $chatState);
            if ($this->acceptAnswer($messageDto)) {
                $messageDto->answer = null;
                $messageDto->callbackData = null;
                $this->questionsRedisManager->updateChatState($messageDto->user->_id, ChatStateEnum::GOAL->value);

                $this->nextHandler->handle($messageDto, ChatStateEnum::GOAL->value);
            }
        } else {
            Log::channel('telegram')->info('Go to goal handler: ' . $chatState);
            $this->nextHandler->handle($messageDto, $chatState);
        }
    }

    private function sendQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->_id;

        if ($messageDto->callbackData === $userId.'_'.SubjectStudiesEnum::QUESTION->value) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, SubjectStudiesEnum::QUESTION->value);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->chat_id,
                'text' => __('bot_messages.subject_of_studies'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
    {
        if (empty($messageDto->answer)) {
            return false;
        }
        
        $userId = $messageDto->user->_id;
        $validateSubject = $this->subjectValidator->validateSubjectTitle($messageDto->answer ?? '');

        switch ($validateSubject) {
            case true:
                $this->questionsRedisManager->setAnswerForQuestion($userId, SubjectStudiesEnum::QUESTION->value, $messageDto->answer, 1);

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->chat_id,
                    'text' => 'Your title of object studies was save✅',
                ]);

                return $validateSubject;

            case false:
                if (is_null($messageDto->answer)) {
                    return $validateSubject;
                }

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->chat_id,
                    'text' => __(
                        'bot_messages.wrong_subject_title',
                        ['email' => $messageDto->answer]
                    ),
                ]);

                return $validateSubject;
        }
    }
}
