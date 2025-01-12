<?php

namespace App\Jobs;

use App\Service\Trello\Boards\BoardClient;
use App\Service\Trello\Lists\ListClient;
use App\Service\Trello\Cards\CardClient;
use App\Models\Mongo\TrelloBoard;
use App\Models\Mongo\User;
use App\Models\Mongo\Workspace;
use App\Repository\Trello\BoardRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
    public function handle(BoardRepository $boardRepository): void {
        $board = $boardRepository->createAndStoreBoard($this->workspace, $this->user);
    }
}
