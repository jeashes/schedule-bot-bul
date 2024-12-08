<?php

namespace App\Managers;

use App\Dto\TelegramChatCurrentStateDto;
use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\SubjectStudiesEnum;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;

class TelegramMessageManager
{
    public function __construct(
        private readonly TelegramStudySubjectMessageManager $studySubjectMessageManger
    ) {}

    public function handleMessages(
        TelegramMessageDto $telegramMessageDto
    ): void {
        $this->botStartMessage($telegramMessageDto);
        $this->subjectStudyMessages($telegramMessageDto);
    }

    private function subjectStudyMessages(TelegramMessageDto $messageDto): void
    {
        $this->studySubjectMessageManger->sendQuestion($messageDto);
        $this->studySubjectMessageManger->clarifyAnswer($messageDto);
        // $this->studySubjectMessageManger->validateAnswer($telegramMessageDto);
        $this->studySubjectMessageManger->acceptAnswer($messageDto);
    }

    private function botStartMessage(TelegramMessageDto $messageDto): void
    {
        if ($messageDto->answer === '/start') {

            Redis::del($messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value);

            Redis::set(
                $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value,
                json_encode([
                    'current_answer' => null,
                    'edited' => null,
                    'approved' => null
                ])
            );

            $keyboard = new InlineKeyboard([
                [
                    'text' => 'LET\'S GOOO',
                    'callback_data' => $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value
                ]
            ]);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    'bot_messages.welcome', [
                        'name' => $messageDto->user->getFirstName()  . ' '
                        . $messageDto->user->getLastName()
                    ]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }
}
