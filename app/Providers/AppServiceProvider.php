<?php

namespace App\Providers;

use App\Events\GameCompleted;
use App\Listeners\NotifyGameCompleted;
use App\Models\Passport\Client;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        Passport::useClientModel(Client::class);

        Event::listen(GameCompleted::class, NotifyGameCompleted::class);
    }
}
