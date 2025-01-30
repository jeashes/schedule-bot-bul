<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Helpers\TelegramHelper;
use App\Service\OpenAi\SubjectValidator;

class SubjectStateHandler
{
    public function __construct(
        private readonly QuestionsRedisManager $questionsRedisManager,
        private readonly SubjectValidator $subjectValidator,
    ) {

    }

    public function sendSubjectQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();

        if ($messageDto->callbackData === $userId . '_' . SubjectStudiesEnum::QUESTION->value) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, SubjectStudiesEnum::QUESTION->value);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.subject_of_studies'),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function acceptSubjectAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();
        $validateSubject =  $this->subjectValidator->validateSubjectTitle($messageDto->answer ?? '');

        switch ($validateSubject) {
            case true:
                $this->questionsRedisManager->setAnswerForQuestion($userId, SubjectStudiesEnum::QUESTION->value, $messageDto->answer, 1);

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => 'Your title of object studies was save✅'
                ]);

                return $validateSubject;

            case false;
                TelegramBotRequest::sendMessage([
                    'chat_id' => $data['user']['chat_id'],
                    'text' => __(
                        'bot_messages.wrong_subject_title',
                        ['email' => $data['answer']]
                    ),
                ]);

                return $validateSubject;
        }
    }
}
