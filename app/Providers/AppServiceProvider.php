<?php

namespace App\Providers;

use App\Helpers\WeekDayDates;
use Illuminate\Support\ServiceProvider;
use Longman\TelegramBot\Telegram;
use App\Repository\TrelloWorkSpaceRepository;
use App\Repository\UserRepository;
use App\Service\Trello\Boards\BoardClient;
use App\Service\Trello\Cards\CardClient;
use App\Service\Trello\Lists\ListClient;
use App\Service\Trello\Organizations\OrganizationClient;
use App\Service\Trello\TrelloConfig;
use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Telegram::class, function($app) {
            return new Telegram(config('telegram.bot_api_token'), config('telegram.bot_username'));
        });

        $this->app->singleton(WeekDayDates::class, function($app) {
            return new WeekDayDates(Carbon::now());
        });

        $this->app->singleton(TrelloConfig::class, function($app) {
            return new TrelloConfig(config('trello.api_key'), config('trello.api_token'));
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
