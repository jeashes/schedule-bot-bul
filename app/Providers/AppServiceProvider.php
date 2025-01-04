<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Longman\TelegramBot\Telegram;
use App\Repository\TrelloWorkSpaceRepository;
use App\Repository\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Telegram::class, function($app) {
            $botApiKey = config('telegram.bot_api_token');
            $botUserName = config('telegram.bot_username');

            return new Telegram($botApiKey, $botUserName);
        });

        $this->app->singleton(TrelloWorkSpaceRepository::class);
        $this->app->singleton(UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
