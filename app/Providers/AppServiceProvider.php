<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Longman\TelegramBot\Telegram;
use App\Managers\Telegram\HoursOnStudyManager;
use App\Managers\Telegram\StudyPaceLevelManager;
use App\Managers\Telegram\StudySubjectMessageManager;
use App\Managers\Telegram\MessageManager as TelegramMessageManager;
use App\Repository\TrelloWorkSpaceRepository;

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

        $this->app->singleton(StudySubjectMessageManager::class);
        $this->app->singleton(HoursOnStudyManager::class);
        $this->app->singleton(StudyPaceLevelManager::class);
        $this->app->singleton(TrelloWorkSpaceRepository::class);
        $this->app->singleton(TelegramMessageManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
