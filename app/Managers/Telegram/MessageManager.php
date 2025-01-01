<?php

namespace App\Managers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\HoursOnStudyEnum;
use App\Enums\Telegram\PaceLevelEnum;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Enums\Telegram\UserEmailEnum;
use App\Managers\Trello\OrganizationManager;
use App\Models\Mongo\User;
use Throwable;
use App\Repository\TrelloWorkSpaceRepository;
use App\Repository\UserRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;

class MessageManager
{
    public function __construct(
        private readonly StudySubjectMessageManager $studySubjectManager,
        private readonly HoursOnStudyManager $hoursOnStudyManager,
        private readonly StudyPaceLevelManager $studyPaceLevelManager,
        private readonly UserEmailManager $userEmailManager,
        private readonly TrelloWorkSpaceRepository $trelloWorkSpaceRepository,
        private readonly UserRepository $userRepository,
        private readonly OrganizationManager $organizationManager
    ) {}

    public function handleMessages(TelegramMessageDto $messageDto): void
    {
        try {
            $this->botStartMessage($messageDto);
            $this->subjectStudyMessages($messageDto);

            $userId = $messageDto->user->getId();

            if ($this->isSubjectStudyApproved($userId)) {
                Log::channel('telegram')->info('Subject name is approved');
                $this->hoursOnStudyMessages($messageDto);
            }

            if ($this->isHoursForStudyApproved($userId)) {
                Log::channel('telegram')->info('Hourse for study is approved');
                $this->paceLevelMessages($messageDto);
            }

            if ($this->isPaceLevelApproved($userId)) {
                $this->userEmailMessages($messageDto);
            }

            if ($this->isUserEmailApproved($userId)) {
                $userEmailInfo = json_decode(
                    Redis::get($userId . '_' . UserEmailEnum::QUESTION->value), true);

                $workspace = $this->trelloWorkSpaceRepository->createWorkspaceByUserId(
                    $this->getWorkspaceParamsByAnswers($userId),
                    $userId
                );

                $this->userRepository->findById($userId)->update([
                    'email' => $userEmailInfo['current_answer']
                ]);

                TelegramBotRequest::sendMessage([
                    'chat_id' => $messageDto->user->getChatId(),
                    'text' => __(
                        'bot_messages.trello_workspace_created', [
                            'name' => $messageDto->user->getFirstName()  . ' '
                            . $messageDto->user->getLastName()
                        ]
                    ),
                    'parse_mode' => 'Markdown'
                ]);
            }
        } catch (Throwable $e) {
            Log::channel('telegram')->error('Something went wrong: ' . $e->getMessage());
            TelegramBotRequest::sendMessage([
                'chat_id' => $messageDto->user->getChatId(),
                'text' => __('bot_messages.error'),
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    private function subjectStudyMessages(TelegramMessageDto $messageDto): void
    {
        $this->studySubjectManager->sendQuestion($messageDto);
        $this->studySubjectManager->clarifyAnswer($messageDto);
        $this->studySubjectManager->acceptAnswer($messageDto);
    }

    private function hoursOnStudyMessages(TelegramMessageDto $messageDto): void
    {
        $this->hoursOnStudyManager->sendQuestion($messageDto);
        $this->hoursOnStudyManager->clarifyAnswer($messageDto);
        $this->hoursOnStudyManager->acceptAnswer($messageDto);
    }

    private function userEmailMessages(TelegramMessageDto $messageDto): void
    {
        $this->userEmailManager->sendQuestion($messageDto);
        $this->userEmailManager->clarifyAnswer($messageDto);
        $this->userEmailManager->acceptAnswer($messageDto);
    }

    private function paceLevelMessages(TelegramMessageDto $messageDto): void
    {
        $this->studyPaceLevelManager->sendQuestion($messageDto);
        $this->studyPaceLevelManager->acceptAnswer($messageDto);
    }

    private function isSubjectStudyApproved(string $userId): bool
    {
        $subjectStudiesInfo = json_decode(Redis::get($userId . '_' . SubjectStudiesEnum::QUESTION->value), true);
        return !empty($subjectStudiesInfo['current_answer']) && !empty($subjectStudiesInfo['approved']);
    }

    private function isHoursForStudyApproved(string $userId): bool
    {
        $hoursOnStudyInfo = json_decode(Redis::get($userId . '_' . HoursOnStudyEnum::QUESTION->value), true);
        return !empty($hoursOnStudyInfo['current_answer']) && !empty($hoursOnStudyInfo['approved']);
    }

    private function isPaceLevelApproved(string $userId): bool
    {
        $paceLevelInfo = json_decode(Redis::get($userId . '_' . PaceLevelEnum::QUESTION->value), true);
        return !empty($paceLevelInfo['current_answer']) && !empty($paceLevelInfo['approved']);
    }

    private function isUserEmailApproved(string $userId): bool
    {
        $userEmailInfo = json_decode(Redis::get($userId . '_' . UserEmailEnum::QUESTION->value), true);
        return !empty($userEmailInfo['current_answer']) && !empty($userEmailInfo['approved']);
    }

    private function getWorkspaceParamsByAnswers(string $userId): array
    {
        $subjectStudiesInfo = json_decode(Redis::get($userId . '_' . SubjectStudiesEnum::QUESTION->value), true);
        $hoursOnStudyInfo = json_decode(Redis::get($userId . '_' . HoursOnStudyEnum::QUESTION->value), true);
        $paceLevelInfo = json_decode(Redis::get($userId . '_' . PaceLevelEnum::QUESTION->value), true);

        return [
            'name' => $subjectStudiesInfo['current_answer'],
            'time_on_scedule' => (int)$hoursOnStudyInfo['current_answer'],
            'pace_level' => $paceLevelInfo['current_answer'],
            'task_ids' => []
        ];
    }

    private function botStartMessage(TelegramMessageDto $messageDto): void
    {
        if ($messageDto->answer === '/start') {
            $this->resetUserAnswers($messageDto->user->getId());
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

    private function resetUserAnswers(string $userId): void
    {
        $this->removeOldAnswers($userId);

        Redis::set(
            $userId . '_' . SubjectStudiesEnum::QUESTION->value,
            json_encode([
                'current_answer' => null,
                'edited' => null,
                'approved' => null,
            ])
        );

        Redis::set(
            $userId . '_' . HoursOnStudyEnum::QUESTION->value,
            json_encode([
                'current_answer' => null,
                'edited' => null,
                'approved' => null
            ])
        );

        Redis::set(
            $userId . '_' . PaceLevelEnum::QUESTION->value,
            json_encode([
                'current_answer' => null,
                'approved' => null
            ])
        );

        Redis::set(
            $userId . '_' . UserEmailEnum::QUESTION->value,
            json_encode([
                'current_answer' => null,
                'edited' => null,
                'approved' => null
            ])
        );
    }

    private function removeOldAnswers(string $userId): void
    {
        Redis::del($userId . '_' . SubjectStudiesEnum::QUESTION->value);
        Redis::del($userId . '_' . HoursOnStudyEnum::QUESTION->value);
        Redis::del($userId . '_' . PaceLevelEnum::QUESTION->value);
        Redis::del($userId . '_' . UserEmailEnum::QUESTION->value);
    }
}
