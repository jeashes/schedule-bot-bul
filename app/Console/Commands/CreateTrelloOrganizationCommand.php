<?php

namespace App\Console\Commands;

use App\Service\Trello\Organizations\OrganizationClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Throwable;

class CreateTrelloOrganizationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-trello-organization-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get or create Trello Organization';

    /**s
     * Execute the console command.
     */
    public function handle(OrganizationClient $client): int
    {
        $displayName = 'Organization for schedule';
        $name = 'Main organization';
        $desription = 'The organization where creates boards for scheduling';
        if ($this->isTrelloOrgAlreadyCreated(config('trello.organization_id'), $client)) {
            $this->info('Organization id already exist in config/trello');
            return SymfonyCommand::FAILURE;
        }

        $response = $client->create(
            displayName: $displayName,
            name: $name,
            description: $desription
        );

        $data = json_decode($response->body(), true);
        $organizationId = $data['id'];

        $this->info("Your organization id successfully created: $organizationId, please add into config/trello");
        return SymfonyCommand::SUCCESS;
    }

    private function isTrelloOrgAlreadyCreated(string $orgId, OrganizationClient $client): bool
    {
        try {
            $response = $client->get($orgId);
            if ($response->status() === 200) {
                Log::channel('trello')->info('Organization already created');

                return true;
            }

            Log::channel('trello')->info('Needs created Trello Organization and add id from response to config');

            return false;
        } catch (Throwable $e) {
            Log::channel('trello')->info('Error during getting organization id from Trello API '
                .$e->getMessage()
            );

            return false;
        }
    }
}
