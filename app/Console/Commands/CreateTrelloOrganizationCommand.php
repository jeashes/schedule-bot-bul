<?php

namespace App\Console\Commands;

use App\Managers\Trello\OrganizationManager;
use App\Models\Mongo\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Predis\Command\Redis\DUMP;
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
    public function handle(OrganizationManager $manager)
    {
        $displayName = 'Zabuliti velika';
        $name = 'Main orga';
        $desription = 'The organization where creates boards for scheduling';
        if ($this->isTrelloOrgAlreadyCreated(config('trello.organization_id'), $manager)) {
            $this->info('Organization id already exist in config/trello');
            die();
        }

        $response = $manager->create(
            displayName: $displayName,
            name: $name,
            description: $desription
        );

        $data = json_decode($response->body(), true);
        $organizationId = $data['id'];

        $this->info("Your organization id successfully created: $organizationId, please add into config/trello");
    }

    private function isTrelloOrgAlreadyCreated(string $orgId, OrganizationManager $organizationManager): bool
    {
        try {
            $response = $organizationManager->get($orgId);
            if ($response->status() === 200) {
                Log::channel('trello')->info('Organization already created');
                return true;
            }

            Log::channel('trello')->info('Needs created Trello Organization and add id from response to config');
            return false;
        } catch (Throwable $e) {
            Log::channel('trello')->info('Error during getting organization id from Trello API '
                . $e->getMessage()
            );
            return false;
        }
    }
}
