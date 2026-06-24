<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Events\UserRegistered;
use App\Listeners\StoreUserNotification;
use  App\Listeners\SendUserWelcomeEmail;
use App\Events\ChangePassword;
use App\Listeners\StorePasswordNotification;
use App\Events\NotifyInfo;
use App\Listeners\NotifyInfoSend;

//\App\Listeners\SendUserWelcomeEmail::class,

class EventServiceProvider extends ServiceProvider
{
     protected $listen = [
        UserRegistered::class => [ StoreUserNotification::class,],
        ChangePassword::class => [ StorePasswordNotification::class,],
        NotifyInfo::class => [ NotifyInfoSend::class,],

    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
