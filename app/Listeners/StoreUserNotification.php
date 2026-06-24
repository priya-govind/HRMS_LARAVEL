<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;


class StoreUserNotification
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
    public function handle(UserRegistered $event)
    {
        if(!empty(session('user_id'))){
        $senderId = session('user_id');
        $sender = User::find($senderId);
        $sender_name = session('user_name');
        $roleName = session('role_name');
        } else {
        $senderId =config('global.superadmin_id');
        $sender = User::find($senderId);
        $sender_name = optional($sender)->name;  
        $roleName = optional($sender)->roles->pluck('role_name')->first(); 
        }

        
        
        // Send password reset link to the registered user
        $status = Password::sendResetLink(['email' => $event->user->email]);

        // Define message based on reset link status
        if ($status === Password::RESET_LINK_SENT) {
            $message = "Your account has been created.<br/>
                        **Username:** " . $event->user->email . "<br/>
                        Please set your password using the reset link sent to your email.";
        } else {
            $message = "Your account has been created.<br/>
                        **Username:** " . $event->user->email . "<br/>
                        However, we encountered an issue sending your password setup email.
                        Please contact support.";
        }

        // Store notification in the database
        DB::table('notification')->insert([
            'notify_type' => 'user_registered',
            'receiver_id' => $event->user->id,
            'sender_id' => $senderId,
            'sender_name' => $sender_name . '-' . $roleName,
            'receiver_name' => $event->user->name,
            'is_read' => config('global.notify_unread'),
            'subject' => 'Fortgrid - New User Registered',
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

}
