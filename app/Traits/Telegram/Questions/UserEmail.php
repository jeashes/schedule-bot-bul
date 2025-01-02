<?php

namespace App\Traits\Telegram\Questions;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\AnswerEditAcceptEnum;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Enums\Telegram\UserEmailEnum;

trait UserEmail
{
    public function sendEmailQuestion(TelegramMessageDto $messageDto): void
    {
        $userEmailInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value), true);

        if (is_null($userEmailInfo['current_answer'])) {
            Redis::set(
                $messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value,
                json_encode([
                    'current_answer' => '',
                    'edited' => 0,
                    'approved' => 0
                ])
            );

            $messageDto->answer = null;

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __(
                    'bot_messages.ask_email',
                    ['triesCount' => 3]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function clarifyEmailAnswer(TelegramMessageDto $messageDto): void
    {
        $userEmailInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value), true);

        if (!empty($messageDto->answer)
            && $userEmailInfo['edited'] < AnswerEditAcceptEnum::EDIT_COUNT_CLARIFY->value
            && $userEmailInfo['approved'] === 0) {

            $keyboard = new InlineKeyboard([
                [
                    'text' => 'Yes',
                    'callback_data' => UserEmailEnum::NAME_ACCEPT->value
                ]
            ]);

            Redis::set(
                $messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $messageDto->answer,
                    'edited' => $userEmailInfo['edited'] + 1,
                    'approved' => $userEmailInfo['approved']
                ])
            );

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'reply_markup' => $keyboard,
                'text' => __(
                    'bot_messages.validate_answer',
                    ['title' => $messageDto->answer]
                ),
                'parse_mode' => 'Markdown'
            ]);
        }

        if (!empty($messageDto->answer)
            && $userEmailInfo['edited'] === AnswerEditAcceptEnum::EDIT_COUNT_CLARIFY->value
            && $userEmailInfo['approved'] === 0) {

            Redis::set(
                $messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $messageDto->answer,
                    'edited' => $userEmailInfo['edited'] + 1,
                    'approved' => $userEmailInfo['approved']
                ])
            );
        }
    }

    public function acceptEmailAnswer(TelegramMessageDto $messageDto): void
    {
        $userEmailInfo = json_decode(
            Redis::get($messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value), true);

        if (($messageDto->callbackData === UserEmailEnum::NAME_ACCEPT->value
            || $userEmailInfo['edited'] >= AnswerEditAcceptEnum::EDIT_COUNT_ACCEPT->value && !empty($messageDto->answer))
            && $userEmailInfo['approved'] === 0) {

            Redis::set(
                $messageDto->user->getId() . '_' . UserEmailEnum::QUESTION->value,
                json_encode([
                    'current_answer' => $userEmailInfo['current_answer'],
                    'edited' => $userEmailInfo['edited'],
                    'approved' => 1
                ])
            );

            $messageDto->answer = null;
        }
    }
}
