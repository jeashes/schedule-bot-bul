<?php

namespace App\Managers;

use App\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\SubjectStudiesEnum;
use Illuminate\Support\Facades\Log;

class TelegramStudySubjectMessageManager
{
    public function sendQuestion(TelegramMessageDto $telegramMessageDto): void
    {
        if ($telegramMessageDto->callbackData === $telegramMessageDto->user->getId()) {
            Redis::set($telegramMessageDto->user->getId(), json_encode([
                'name' => SubjectStudiesEnum::KEY_NAME->value,
                'current_answer' => '',
                'previous_answer' => '',
                'edited' => 0
            ]));

            TelegramBotRequest::sendMessage([
                'chat_id' => $telegramMessageDto->user->getChatId(),
                'text' => __(
                    'bot_messages.subject_of_studies',
                    ['triesCount' => 3]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function clarifyAnswer(TelegramMessageDto $telegramMessageDto): void
    {
        $subjectStudiesInfo = json_decode(Redis::get($telegramMessageDto->user->getId()), true);
        if (!empty($telegramMessageDto->answer) && !empty($subjectStudiesInfo) && $subjectStudiesInfo['edited'] < 2) {
            $keyboard = new InlineKeyboard([
                [
                    'text' => 'Yes',
                    'callback_data' => SubjectStudiesEnum::NAME_ACCEPT->value
                ],
                [
                    'text' => 'No',
                    'callback_data' => SubjectStudiesEnum::NAME_DECLINE->value
                ]
            ]);

            Redis::set($telegramMessageDto->user->getId(), json_encode([
                'name' => SubjectStudiesEnum::KEY_NAME->value,
                'current_answer' => $telegramMessageDto->answer,
                'previous_answer' => $subjectStudiesInfo['previous_answer'],
                'edited' => $subjectStudiesInfo['edited']
            ]));

            TelegramBotRequest::sendMessage([
                'chat_id' => $telegramMessageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    'bot_messages.validate_answer',
                    ['title' => $telegramMessageDto->answer]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function validateAnswer(TelegramMessageDto $telegramMessageDto): void
    {
        $subjectStudiesInfo = json_decode(Redis::get($telegramMessageDto->user->getId()), true);

        if ($telegramMessageDto->callbackData === SubjectStudiesEnum::NAME_DECLINE->value && ($subjectStudiesInfo['edited'] ?? 0) <= 2) {
            $triesCount = ($subjectStudiesInfo['edited'] ?? 0) + 1;
            $currentAnswer = $subjectStudiesInfo['current_answer'] ?? '';

            Redis::set($telegramMessageDto->user->getId(), json_encode([
                'name' => SubjectStudiesEnum::KEY_NAME->value,
                'current_answer' => '',
                'previous_answer' => $currentAnswer,
                'edited' => $triesCount
            ]));

            $this->sendQuestionAgain($telegramMessageDto, 3 - $triesCount);
        }
    }

    private function sendQuestionAgain(TelegramMessageDto $telegramMessageDto, int $editedCount): void
    {
        if ($telegramMessageDto->callbackData === SubjectStudiesEnum::NAME_DECLINE->value && $editedCount > 0) {
            TelegramBotRequest::sendMessage([
                'chat_id' => $telegramMessageDto->user->getChatId(),
                'text' => __(
                    'bot_messages.subject_of_studies',
                    ['triesCount' => $editedCount]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function acceptAnswer(TelegramMessageDto $telegramMessageDto): void
    {
        $subjectStudiesInfo = json_decode(Redis::get($telegramMessageDto->user->getId()), true);

        if ($telegramMessageDto->callbackData === SubjectStudiesEnum::NAME_ACCEPT->value || ($subjectStudiesInfo['edited'] ?? 0) === 2 && !empty($telegramMessageDto->answer)) {
                Redis::set($telegramMessageDto->user->getId(), json_encode([
                    'name' => SubjectStudiesEnum::KEY_NAME->value,
                    'current_answer' => $telegramMessageDto->answer,
                    'previous_answer' => $subjectStudiesInfo['previous_answer'],
                    'edited' => $subjectStudiesInfo['edited'] + 1
                ]));

                TelegramBotRequest::sendMessage([
                    'chat_id' => $telegramMessageDto->user->getChatId(),
                    'text' => 'Your title of object studies was save'
                ]);
            }
    }
}
