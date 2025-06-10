<?php

namespace App\Providers;

use App\Helpers\WeekDayDates;
use App\Repository\TrelloWorkSpaceRepository;
use App\Repository\UserRepository;
use App\Service\Google\GoogleConfig;
use App\Service\Trello\TrelloConfig;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Longman\TelegramBot\Telegram;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Telegram::class, function ($app) {
            return new Telegram(config('telegram.bot_api_token'), config('telegram.bot_username'));
        });

        $this->app->bind(WeekDayDates::class, function ($app) {
            return new WeekDayDates(Carbon::now()->addWeek()->startOfWeek(Carbon::MONDAY));
        });

        $this->app->singleton(TrelloConfig::class, function ($app) {
            return new TrelloConfig(config('trello.api_key'), config('trello.api_token'));
        });

        $this->app->singleton(GoogleConfig::class, function ($app) {
            return new GoogleConfig(config('google.api_key'), config('google.cx'));
        });

        $this->app->singleton(TrelloWorkSpaceRepository::class);
        $this->app->singleton(UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
