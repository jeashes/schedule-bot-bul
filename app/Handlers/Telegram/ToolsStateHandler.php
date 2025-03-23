<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Enums\Telegram\ToolsEnum;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Service\OpenAi\SubjectToolsValidator;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Request as TelegramBotRequest;

class ToolsStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly CourseTypeStateHandler $nextHandler,
        private readonly QuestionsRedisManager $questionsRedisManager,
        private readonly SubjectToolsValidator $subjectToolsValidator
    ) {}

    public function handle(TelegramMessageDto $messageDto, int $chatState): void
    {
        if ($chatState === ChatStateEnum::TOOLS->value) {
            $this->sendQuestion($messageDto);
            if ($this->acceptAnswer($messageDto)) {
                $this->questionsRedisManager->updateChatState($messageDto->user->getId(), ChatStateEnum::COURSE_TYPE->value);

                $this->nextHandler->handle($messageDto, ChatStateEnum::COURSE_TYPE->value);
            } else {
                $this->nextHandler->handle($messageDto, ChatStateEnum::COURSE_TYPE->value);
            }
        }
    }

    private function sendQuestion(TelegramMessageDto $messageDto): void
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

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
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
