<?php

namespace App\Listeners;

use App\Events\ChangePassword;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StorePasswordNotification
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
  public function handle(ChangePassword $event)
    {
       
        $senderId = !empty(session('user_id')) ? session('user_id') : config('global.superadmin_id') ;
        $sender = User::find($senderId);
        $sender_name = optional($sender)->name;
        $roleName = optional($sender)->roles->pluck('role_name')->first();
        // Send password reset link to the registered user
        $status = Password::sendResetLink(['email' => $event->user->email]);

        // Define message based on reset link status
        if ($status === Password::RESET_LINK_SENT) {
            $message = "Your Password was changed Successfully.<br/>
                        **Username:** " . $event->user->email ;
        } else {
            $message = "Your Password was changed Successfully.<br/>
                        **Username:** " . $event->user->email;
        }

        // Store notification in the database
        DB::table('notification')->insert([
            'notify_type' => 'user_registered',
            'receiver_id' => $event->user->id,
            'sender_id' => $senderId,
            'sender_name' => $sender_name . '-' . $roleName,
            'receiver_name' => $event->user->name,
            'is_read' => config('global.notify_unread'),
            'subject' => 'Fortgrid - Password Changed.',
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
