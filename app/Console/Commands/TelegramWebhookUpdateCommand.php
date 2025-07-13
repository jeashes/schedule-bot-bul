<?php

namespace App\Console\Commands;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class TelegramWebhookUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:telegram-webhook-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update webhook for bot because we use dynamic route';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $botToken = config('telegram.bot_api_token');
        $botWebhook = config('telegram.bot_webhook');
        $telegramSecret = config('telegram.secret_token');

        $queryParams = [
            'url' => "{$botWebhook}/api/webhook/{$telegramSecret}",
        ];

        try {
            $response = Http::baseUrl('https://api.telegram.org')->get("/bot$botToken/setWebhook", $queryParams);

            if ($response->successful()) {
                $this->info('Webhook for bot was successfully updated');

                return SymfonyCommand::SUCCESS;
            }
            $this->error('Telegram webhook was not updated, something went wrong');

            return SymfonyCommand::FAILURE;

        } catch (RequestException $e) {
            Log::channel('telegram')->error('Something went wrong during updating webhook for bot', ['error' => $e->getMessage()]);
        }
    }
}
