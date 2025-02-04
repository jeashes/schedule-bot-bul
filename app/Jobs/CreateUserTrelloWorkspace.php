<?php

namespace App\Jobs;

use App\Dto\Trello\CardDto;
use App\Enums\Trello\InviteTypeEnum;
use App\Helpers\WeekDayDates;
use App\Service\Trello\Boards\BoardClient;
use App\Models\Mongo\User;
use Longman\TelegramBot\Request as TelegramBotRequest;
use App\Models\Mongo\Workspace;
use App\Repository\Trello\BoardRepository;
use App\Repository\Trello\CardRepository;
use App\Repository\Trello\ListRepository;
use App\Service\Trello\Cards\CardClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateUserTrelloWorkspace implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Workspace $workspace, private User $user)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(
        BoardRepository $boardRepository,
        BoardClient $boardClient,
        CardClient $cardClient,
        ListRepository $listRepository,
        CardRepository $cardRepository,
        WeekDayDates $weekDayDates,
    ): void {
        try {
            $userId = $this->user->getId();
            $board = $boardRepository->createAndStoreBoard($this->workspace, $this->user);
            $boardClient->inviteMemberViaEmail(
                $board->trello_id, $this->user->getEmail(), InviteTypeEnum::NORMAL->value
            );

            $lists = json_decode($boardClient->getLists($board->trello_id), true);
            $listRepository->saveDefaultLists($userId, $lists);
            $toDoList = $listRepository->getToDoList($userId);

            $scheduleDays = $weekDayDates->getDatesBySchedule($this->workspace->getSchedule());

            try {
                foreach ($scheduleDays as $scheduleDay) {
                    sleep(0.5);
                    $response = $cardClient->createNewCard(
                        idList: $toDoList->trello_id,
                        name: "Empty name of lesson",
                        dueDate: $scheduleDay
                    );

                    $data = json_decode($response, true);

                    $card = $cardRepository->saveCard($userId, new CardDto($data));

                    $this->workspace->addTaskId($card->getId());
                }
            } catch (Throwable $e) {
                Log::error($e->getMessage());
            }

            TelegramBotRequest::sendMessage([
                'chat_id' => $this->user->getChatId(),
                'text' => 'Your tasks on next two weeks were successfully created!',
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
