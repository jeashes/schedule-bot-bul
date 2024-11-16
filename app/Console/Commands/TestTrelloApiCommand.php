<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Semaio\TrelloApi\ClientBuilder;

class TestTrelloApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-trello-api-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new ClientBuilder();
        $client = $client->build(
            config('trello.api_key'),
            config('trello.api_token')
        );

        dump($client->getMemberApi()->boards()->all());
    }
}
