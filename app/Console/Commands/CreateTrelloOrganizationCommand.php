<?php

namespace App\Console\Commands;

use App\Managers\Trello\OrganizationManager;
use App\Models\Mongo\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $displayName = 'Schedule Bot Bull';
        $name = 'Main organization';
        $desription = 'The organization where creates boards for scheduling';
        $organizationId = config('trello.organization_id');

        if (!$this->isTrelloOrgAlreadyCreated($organizationId, $manager)) {
            $organizationId = $manager->create(
                displayName: $displayName,
                name: $name,
                description: $desription
            );
        }

        try {
            Organization::query()->createOrFirst([
                'display_name' => $displayName,
                'name' => $name,
                'description' => $desription,
                'trello_id' => $organizationId
            ]);

            Log::channel('trello')->info('Main Trello Organization was successfuly created');
        } catch (Throwable $exception) {
            Log::channel('trello')->error('Something went wrong', [
                'error'=> $exception->getMessage(),
                'organizationTrelloId' => $organizationId
            ]);
        }
    }

    private function isTrelloOrgAlreadyCreated(string $orgId, OrganizationManager $organizationManager): bool
    {
        try {
            $response = $organizationManager->get($orgId);
            if (!$response->status() === 200) {
                Log::channel('trello')->info('Needs created Trello Organization and add id from response to config');
                return false;
            }

            Log::channel('trello')->info('Organization already created');
            return true;
        } catch (Throwable $e) {
            Log::channel('trello')->info('Error during getting organization id from Trello API '
                . $e->getMessage()
            );
            return false;
        }
    }
}
