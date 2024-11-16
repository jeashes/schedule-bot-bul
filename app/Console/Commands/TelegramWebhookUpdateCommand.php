<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TelegramWebhookUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:telegram-webhook-update-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update webhook for bot because we use dynamic route';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $botToken = config('telegram.bot_api_token');
        $botWebhook = config('telegram.bot_webhook');

        $client = new Client(['base_uri' => 'https://api.telegram.org']);
        try {
            $client->request('GET', "/bot$botToken/setWebhook", [
                'query' => [
                    'url' => $botWebhook
                ]
            ]);

            Log::channel('telegram')->info('Webhook for bot was successfully updated');
        } catch (RequestException $e) {
            Log::channel('telegram')->error(
                'Something went wrong during updating webhook for bot',
                ['error' => $e->getMessage()]
            );
        }
    }
}
