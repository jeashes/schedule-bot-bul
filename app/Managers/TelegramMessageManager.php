<?php

namespace App\Managers;

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

    public function handleMessages(TelegramMessageDto $telegramMessageDto): void
    {
        $this->botStartMessage($telegramMessageDto);
        $this->subjectStudyMessages($telegramMessageDto);
    }

    private function subjectStudyMessages(TelegramMessageDto $telegramMessageDto): void
    {
        $this->studySubjectMessageManger->sendQuestion($telegramMessageDto);
        $this->studySubjectMessageManger->clarifyAnswer($telegramMessageDto);
        $this->studySubjectMessageManger->validateAnswer($telegramMessageDto);
        $this->studySubjectMessageManger->acceptAnswer($telegramMessageDto);
    }

    private function botStartMessage(TelegramMessageDto $telegramMessageDto): void
    {
        if ($telegramMessageDto->answer === '/start') {

            Redis::del($telegramMessageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value);

            $keyboard = new InlineKeyboard([
                [
                    'text' => 'LET\'S GOOO',
                    'callback_data' => $telegramMessageDto->user->getId()
                ]
            ]);

            TelegramBotRequest::sendMessage([
                'chat_id' => $telegramMessageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    'bot_messages.welcome', [
                        'name' => $telegramMessageDto->user->getFirstName()  . ' '
                        . $telegramMessageDto->user->getLastName()
                    ]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }
}
