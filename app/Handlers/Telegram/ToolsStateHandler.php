<?php

namespace App\Handlers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\ChatStateEnum;
use App\Enums\Telegram\KnowledgeLevelEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Enums\Telegram\ToolsEnum;
use App\Interfaces\Telegram\StateHandlerInterface;
use App\Managers\Telegram\QuestionsRedisManager;
use App\Service\OpenAi\SubjectToolsValidator;
use Illuminate\Support\Facades\Log;
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
        $userId = $messageDto->user->getId();
        $previousAnswer = $this->questionsRedisManager->getPreviousAnswer($userId, KnowledgeLevelEnum::QUESTION->value);

        if ($chatState === ChatStateEnum::TOOLS->value && $previousAnswer) {
            $this->sendQuestion($messageDto);
            Log::channel('telegram')->info('Current tools state: '.$chatState);
            if ($this->acceptAnswer($messageDto)) {
                $messageDto->answer = null;
                $messageDto->callbackData = null;
                $this->questionsRedisManager->updateChatState($userId, ChatStateEnum::COURSE_TYPE->value);

                $this->nextHandler->handle($messageDto, ChatStateEnum::COURSE_TYPE->value);
            }
        } else {
            Log::channel('telegram')->info('Go to course type handler: '.$chatState);
            $this->nextHandler->handle($messageDto, $chatState);
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
                'text' => $this->questionsRedisManager->getBotPhraseByKey($messageDto->languageCode, 'tools_for_study'),
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    private function acceptAnswer(TelegramMessageDto $messageDto): bool
    {
        if (empty($messageDto->answer)) {
            return false;
        }

        $userId = $messageDto->user->getId();
        $subjectInfo = json_decode(Redis::get($userId.'_'.SubjectStudiesEnum::QUESTION->value), true);
        $tools = $this->subjectToolsValidator->validateToolsForStudy($subjectInfo['current_answer'] ?? '', $messageDto->answer ?? '');

        switch ($tools) {
            case true:
                $this->questionsRedisManager->setAnswerForQuestion($userId, ToolsEnum::QUESTION->value, $messageDto->answer, 1);

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => $this->questionsRedisManager->getBotPhraseByKey($messageDto->languageCode, 'tools_saved'),
                ]);

                return $tools;

            case false:
                if (is_null($messageDto->answer)) {
                    return $tools;
                }

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => $this->questionsRedisManager->getBotPhraseByKey($messageDto->languageCode, 'wrong_tools_for_study'),
                ]);

                return $tools;
        }
    }
}
