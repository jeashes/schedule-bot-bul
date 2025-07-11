<?php

namespace App\Jobs;

use App\Dto\Trello\CardDto;
use App\Enums\Trello\InviteTypeEnum;
use App\Helpers\WeekDayDates;
use App\Models\Mongo\User;
use App\Models\Mongo\Workspace;
use App\Repository\Trello\BoardRepository;
use App\Repository\Trello\CardRepository;
use App\Repository\Trello\ListRepository;
use App\Service\OpenAi\MakeTasks;
use App\Service\Trello\Boards\BoardClient;
use App\Service\Trello\Cards\CardClient;
use App\Service\Trello\CheckLists\CheckListClient;
use App\Service\Trello\Members\MemberClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\Telegram;
use Throwable;

class CreateUserTrelloWorkspace implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Workspace $workspace, private User $user) {}

    /**
     * Execute the job.
     */
    public function handle(
        BoardRepository $boardRepository,
        BoardClient $boardClient,
        MemberClient $memberClient,
        CardClient $cardClient,
        CheckListClient $checklistClient,
        ListRepository $listRepository,
        CardRepository $cardRepository,
        WeekDayDates $weekDayDates,
        MakeTasks $makeTasks,
        Telegram $telegram,
    ): void {
        try {
            $userId = $this->user->_id;
            $board = $boardRepository->createAndStoreBoard($this->workspace, $this->user);

            $this->uploadAndUpdateBackground($board->trello_id, $boardClient, $memberClient);

            $lists = json_decode($boardClient->getLists($board->trello_id), true);
            $listRepository->saveDefaultLists($userId, $lists);
            $toDoList = $listRepository->getToDoList($userId);

            $scheduleDays = $weekDayDates->getDatesBySchedule($this->workspace->getSchedule());
            $tasks = $makeTasks->genTasksByAi(
                $this->workspace->getName(),
                $this->workspace->getGoal(),
                $this->workspace->getKnowledgeLevel(),
                $this->workspace->getTools(),
                $this->workspace->getCourseType(),
                count($scheduleDays),
                $this->workspace->getTimeOnSchedule()
            );

            for ($i = 0; $i < count($tasks); $i++) {
                sleep(0.5);
                $cardResponse = $cardClient->createNewCard(
                    idList: $toDoList->trello_id,
                    name: $tasks[$i]['Lesson']['Learning Objectives'],
                    desc: $tasks[$i]['Lesson']['Context Info'],
                    dueDate: $scheduleDays[$i]
                );
                $cardData = json_decode($cardResponse, true);

                $checkListResponse = $cardClient->createCheckList($cardData['id'], 'Tasks');
                $checkListData = json_decode($checkListResponse, true);
                $cardData['idCheckList'] = $checkListData['id'];
                $cardData['checkItems'] = $checkListData['checkItems'];

                $checkItemResponse = $checklistClient->createCheckItem($cardData['idCheckList'], $tasks[$i]['Lesson']['Tiny Task']);
                $checkItemData = json_decode($checkItemResponse, true);

                $cardData['checkItems'][] = $checkItemData['id'];
                $card = $cardRepository->saveCard($userId, new CardDto($cardData));

                $this->workspace->addTaskId($card->_id);
            }

            $boardClient->inviteMemberViaEmail(
                $board->trello_id, $this->user->getEmail(), InviteTypeEnum::NORMAL->value
            );

            TelegramBotRequest::initialize($telegram);
            TelegramBotRequest::sendMessage([
                'chat_id' => $this->user->getChatId(),
                'text' => "Check your mail, tasks on next two weeks were successfully created!\nYour board: {$board->url}",
            ]);
        } catch (Throwable $e) {
            Log::channel('trello')->error($e->getMessage(), ['backtrace' => $e->getTraceAsString()]);
        }
    }

    private function uploadAndUpdateBackground(string $boardTrelloId, BoardClient $boardClient, MemberClient $memberClient): void
    {
        $adminMemberId = json_decode($boardClient->getMembers($boardTrelloId), true)[0]['id'];
        Log::channel('trello')->debug('Admin member id was got: '.$adminMemberId);
        $backgroundTrelloId = json_decode($memberClient->uploadMemberNewBoardBackground($adminMemberId, 'default_background.jpeg'), true)['id'];
        Log::channel('trello')->debug('Background was uploaded and id was got: '.$backgroundTrelloId);
        $boardClient->updateBoard($boardTrelloId, $backgroundTrelloId);
        Log::channel('trello')->debug('Successfully update background for board: '.$boardTrelloId);
    }
}
