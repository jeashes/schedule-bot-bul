<?php

namespace App\Providers;

use App\Helpers\WeekDayDates;
use Illuminate\Support\ServiceProvider;
use Longman\TelegramBot\Telegram;
use App\Repository\TrelloWorkSpaceRepository;
use App\Repository\UserRepository;
use Illuminate\Support\Carbon;

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

        $this->app->singleton(WeekDayDates::class, function($app) {
            return new WeekDayDates(Carbon::now());
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
