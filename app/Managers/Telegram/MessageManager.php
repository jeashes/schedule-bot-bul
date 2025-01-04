<?php

namespace App\Managers\Telegram;

use App\Dto\TelegramMessageDto;
use App\Enums\Telegram\SubjectStudiesEnum;
use App\Enums\Telegram\UserEmailEnum;
use Throwable;
use App\Repository\TrelloWorkSpaceRepository;
use App\Repository\UserRepository;
use App\Traits\Telegram\AnswerApprovedValidate;
use App\Traits\Telegram\Questions\HoursOnStudy;
use App\Traits\Telegram\Questions\StudySchedule;
use App\Traits\Telegram\Questions\StudySubject;
use App\Traits\Telegram\Questions\UserEmail;
use App\Traits\Telegram\ResetUserAnswers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request as TelegramBotRequest;

class MessageManager
{
    use AnswerApprovedValidate;
    use ResetUserAnswers;
    use StudySubject;
    use HoursOnStudy;
    use StudySchedule;
    use UserEmail;

    public function __construct(
        private readonly TrelloWorkSpaceRepository $trelloWorkSpaceRepository,
        private readonly UserRepository $userRepository
    ) {}

    public function handleMessages(TelegramMessageDto $messageDto): void
    {
        try {
            $this->botStartMessage($messageDto);

            $this->sendSubjectQuestion($messageDto);
            $this->clarifySubjectAnswer($messageDto);
            $this->acceptSubjectAnswer($messageDto);

            $userId = $messageDto->user->getId();

            if ($this->isSubjectStudyApproved($userId)) {
                $this->sendHoursQuestion($messageDto);
                $this->clarifyHoursAnswer($messageDto);
                $this->acceptHoursAnswer($messageDto);
            }

            if ($this->isHoursForStudyApproved($userId)) {
                $this->sendScheduleQuestion($messageDto);
                $this->acceptScheduleAnswer($messageDto);
            }

            if ($this->isScheduleApproved($userId)) {
                $this->sendEmailQuestion($messageDto);
                $this->validateEmailAnswer($messageDto);
                $this->clarifyEmailAnswer($messageDto);
                $this->acceptEmailAnswer($messageDto);
            }

            if ($this->isUserEmailApproved($userId)) {
                $userEmailInfo = json_decode(
                    Redis::get($userId . '_' . UserEmailEnum::QUESTION->value), true);

                $workspaceParams = $this->trelloWorkSpaceRepository->getWorkspaceParamsFromRedis($userId);
                $workspace = $this->trelloWorkSpaceRepository->createWorkspaceByUserId(
                    $workspaceParams,
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
}
