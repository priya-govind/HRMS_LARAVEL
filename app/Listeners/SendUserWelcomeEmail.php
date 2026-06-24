<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendUserWelcomeEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */

      public function handle(UserRegistered $event) : void
    {
        $user = $event->user;

        Mail::to($user->email)->send(new \App\Mail\UserWelcomeMail($user));
    }
}
