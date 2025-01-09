<?php

namespace App\Jobs;

use App\Managers\Trello\BoardManager;
use App\Managers\Trello\ListManager;
use App\Managers\Trello\CardManager;
use App\Models\Mongo\TrelloBoard;
use App\Models\Mongo\User;
use App\Models\Mongo\Workspace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        BoardManager $boardManager, ListManager $listManager, CardManager $cardManager
    ): void {
        $response = $boardManager->createBoard(
            name: $this->workspace->getName(),
            desc: 'test description',
            idOrganization: config('trello.organization_id')
        );

        $data = json_decode($response, true);

        TrelloBoard::query()->firstOrCreate([
            'user_id' => $this->user->getId(),
            'trello_id' => $data['id'],
            'name' => $data['name'],
            'desc' => $data['desc']
        ]);
    }
}
