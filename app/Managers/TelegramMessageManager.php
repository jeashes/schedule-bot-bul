<?php

namespace App\Managers;

use App\Dto\TelegramChatCurrentStateDto;
use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\PaceLevelEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Managers\TelegramStudyPaceLevelManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\Telegram;

class TelegramMessageManager
{
    public function __construct(
        private readonly TelegramStudySubjectMessageManager $studySubjectMessageManger,
        private readonly TelegramHoursOnStudyManager $hoursOnStudyMessageManger,
        private readonly TelegramStudyPaceLevelManager $studyPaceLevelManager
    ) {}

    public function handleMessages(TelegramMessageDto $telegramMessageDto): void
    {
        $this->botStartMessage($telegramMessageDto);
        $this->subjectStudyMessages($telegramMessageDto);

        if ($this->isSubjectStudyApproved($telegramMessageDto)) {
            Log::channel('telegram')->info('Subject name is approved');
            $this->hoursOnStudyMessages($telegramMessageDto);
        }

        if ($this->isHoursForStudyApproved($telegramMessageDto)) {
            Log::channel('telegram')->info('Hourse for study is approved');
            $this->paceLevelMessages($telegramMessageDto);
        }
    }

    private function subjectStudyMessages(TelegramMessageDto $messageDto): void
    {
        $this->studySubjectMessageManger->sendQuestion($messageDto);
        $this->studySubjectMessageManger->clarifyAnswer($messageDto);
        $this->studySubjectMessageManger->acceptAnswer($messageDto);
    }

    private function hoursOnStudyMessages(TelegramMessageDto $messageDto): void
    {
        $this->hoursOnStudyMessageManger->sendQuestion($messageDto);
        $this->hoursOnStudyMessageManger->clarifyAnswer($messageDto);
        $this->hoursOnStudyMessageManger->acceptAnswer($messageDto);
    }

    private function paceLevelMessages(TelegramMessageDto $messageDto): void
    {
        $this->studyPaceLevelManager->sendQuestion($messageDto);
        $this->studyPaceLevelManager->acceptAnswer($messageDto);
    }

    private function isSubjectStudyApproved(TelegramMessageDto $messageDto): bool
    {
        $subjectStudiesInfo = json_decode(Redis::get($messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value), true);
        return !empty($subjectStudiesInfo['current_answer']) && !empty($subjectStudiesInfo['approved']);
    }

    private function isHoursForStudyApproved(TelegramMessageDto $messageDto): bool
    {
        $subjectStudiesInfo = json_decode(Redis::get($messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value), true);
        return !empty($subjectStudiesInfo['current_answer']) && !empty($subjectStudiesInfo['approved']);
    }

    private function botStartMessage(TelegramMessageDto $messageDto): void
    {
        if ($messageDto->answer === '/start') {

            Redis::del($messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value);
            Redis::del($messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value);
            Redis::del($messageDto->user->getId() . '_' . PaceLevelEnum::QUESTION->value);

            Redis::set(
                $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value,
                json_encode([
                    'current_answer' => null,
                    'edited' => null,
                    'approved' => null,
                ])
            );

            Redis::set(
                $messageDto->user->getId() . '_' . HoursOnStudyEnum::QUESTION->value,
                json_encode([
                    'current_answer' => null,
                    'edited' => null,
                    'approved' => null
                ])
            );

            Redis::set(
                $messageDto->user->getId() . '_' . PaceLevelEnum::QUESTION->value,
                json_encode([
                    'current_answer' => null,
                    'approved' => null
                ])
            );

            $keyboard = new InlineKeyboard([
                [
                    'text' => 'LET\'S GOOO',
                    'callback_data' => $messageDto->user->getId() . '_' . SubjectStudiesEnum::QUESTION->value
                ]
            ]);

            $messageDto->answer = null;

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
