<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Managers\Telegram\QuestionsRedisManager;
use Illuminate\Support\Facades\Redis;
use App\Enums\Telegram\ToolsEnum;
use App\Service\OpenAi\SubjectToolsValidator;
use Longman\TelegramBot\Request as TelegramBotRequest;

class ToolsStateHandler
{
    public function __construct(
        private readonly QuestionsRedisManager $questionsRedisManager,
        private readonly SubjectToolsValidator $subjectToolsValidator
    ) {}

    public function sendToolsQuestion(TelegramMessageDto $messageDto): void
    {
        $userId = $messageDto->user->getId();
        $toolsInfo = json_decode(Redis::get($userId.'_'.ToolsEnum::QUESTION->value), true);

        if (is_null($toolsInfo['current_answer'])) {

            $this->questionsRedisManager->setAnswerForQuestion($userId, ToolsEnum::QUESTION->value);

            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.tools_for_study'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    public function acceptToolsAnswer(TelegramMessageDto $messageDto): bool
    {
        $userId = $messageDto->user->getId();
        $subjectInfo = json_decode(Redis::get($userId.'_'.SubjectStudiesEnum::QUESTION->value), true);
        $tools = $this->subjectToolsValidator->validateToolsForStudy($subjectInfo['current_answer'] ?? '', $messageDto->answer ?? '');

        switch ($tools) {
            case true:
                $this->questionsRedisManager->setAnswerForQuestion($userId, ToolsEnum::QUESTION->value, $messageDto->answer, 1);

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => 'Your description of tools was save✅',
                ]);

                return $tools;

            case false:
                if (is_null($messageDto->answer)) {
                    return $tools;
                }

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __('bot_messages.wrong_tools_for_study'),
                ]);

                return $tools;
        }
    }
}
