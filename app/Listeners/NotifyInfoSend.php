<?php

namespace App\Listeners;

use App\Events\NotifyInfo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use App\Notifications\NotifyInfoSendMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class NotifyInfoSend
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }
  use InteractsWithQueue;

     public function handle(NotifyInfo $event)
    {
          
        if(!empty(session('user_id'))){
            $senderId = session('user_id');
            $sender = User::find($senderId);
            $sender_name = session('user_name');
            $roleName = session('role_name');
            $sender_mail_id= session('email');
        } else {
            $senderId =config('global.superadmin_id');
            $sender = User::find($senderId);
            $sender_name = optional($sender)->name; 
            $sender_mail_id=$sender->email; 
            $roleName = optional($sender)->roles->pluck('role_name')->first(); 
        }
        $notifyType = $event->info['notify_type'] ?? null;
        // Get team_types details
        
        if(!empty($notifyType) && $notifyType=='team_type'){
             $teamTypes =  $event->info['team_types'] ?? null;
              $pm = User::find($teamTypes->pm_id); // Fetch PM using pm_id
            $receiver_mail_id=$pm->email;
            $data=[];
             $message = "New Team Type is Alerted to you.<br/>
                        Team Type Name: " . $teamTypes->team_typ_name . "<br/>
                       Please <a href='" . route('teams.list_teams') . "'> Click Here </a> to know more details about your teams.";
            $subject='New Team Type Assigned to You';
            $data['subject']=$subject;
            $data['to_addres']=$receiver_mail_id;
            $data['receiver_name']=optional($pm)->name ?? 'Unknown';
            $data['from_address']=$sender_mail_id;
            $data['sender_name']= $sender_name . '-' . $roleName;
            $data['message']=$message;
            // Store notification in the database
                DB::table('notification')->insert([
                    'notify_type' => $notifyType,
                    'receiver_id' => $pm->id,
                    'sender_id' => $senderId,
                    'sender_name' => $sender_name . '-' . $roleName,
                    'receiver_name' =>  optional($pm)->name ?? 'Unknown',
                    'is_read' => config('global.notify_unread'),
                    'subject' => 'Fortgrid - New Team Type Alerted.',
                    'message' => $message,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            $pm->notify(new NotifyInfoSendMail($notifyType, $data));
        }
        if(!empty($notifyType) && $notifyType=='teams'){
            $emp =  $event->info['user'] ?? null;
            $receiver_mail_id=$emp->email;
            $data=[];
             $message = "New Team is Created.<br/>
                        Team Name: " .  $event->info['team_name'] . "<br/>
                        Please <a href='" . route('teams.list_teams') . "'> Click Here </a> to know more details about your teams";
            $subject='New Team Created.';
            $data['subject']=$subject;
            $data['to_addres']=$receiver_mail_id;
            $data['receiver_name']=optional($emp)->name ?? 'Unknown';
            $data['from_address']=$sender_mail_id;
             $data['sender_name']= $sender_name . '-' . $roleName;
            $data['message']=$message;
            // Store notification in the database
                DB::table('notification')->insert([
                    'notify_type' => $notifyType,
                    'receiver_id' => $emp->id,
                    'sender_id' => $senderId,
                    'sender_name' => $sender_name . '-' . $roleName,
                    'receiver_name' =>  optional($emp)->name ?? 'Unknown',
                    'is_read' => config('global.notify_unread'),
                    'subject' => 'Fortgrid - New Team Created.',
                    'message' => $message,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            $emp->notify(new NotifyInfoSendMail($notifyType, $data));
        }
        if(!empty($notifyType) && $notifyType=='user_register'){
            $emp =  $event->info['user'] ?? null;
            $receiver_mail_id=$emp->email;
            $data=[];
            $status = Password::sendResetLink(['email' => $emp->email]);
        // Define message based on reset link status
        if ($status === Password::RESET_LINK_SENT) {
            $message = "Your account has been created.<br/>
                        **Username:** " . $emp->email . "<br/>
                        Please set your password using the reset link sent to your email.";
        } else {
            $message = "Your account has been created.<br/>
                        **Username:** " . $emp->email . "<br/>
                        However, we encountered an issue sending your password setup email.
                        Please contact support.";
        }
            
            $data['subject']='Fortgrid - New User Registered';
            $data['to_addres']=$receiver_mail_id;
            $data['receiver_name']=optional($emp)->name ?? 'Unknown';
            $data['from_address']=$sender_mail_id;
             $data['sender_name']= $sender_name . '-' . $roleName;
            $data['message']=$message;
            // Store notification in the database
                DB::table('notification')->insert([
                    'notify_type' => $notifyType,
                    'receiver_id' => $emp->id,
                    'sender_id' => $senderId,
                    'sender_name' => $sender_name . '-' . $roleName,
                    'receiver_name' =>  optional($emp)->name ?? 'Unknown',
                    'is_read' => config('global.notify_unread'),
                    'subject' => 'Fortgrid - New User Registered',
                    'message' => $message,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            $emp->notify(new NotifyInfoSendMail($notifyType, $data));
        }
        if(!empty($notifyType) && $notifyType=='password_reset'){
            $emp =  $event->info['user'] ?? null;
            $receiver_mail_id=$emp->email;
            $data=[];
            $status = Password::sendResetLink(['email' =>$receiver_mail_id]);

        // Define message based on reset link status
        if ($status === Password::RESET_LINK_SENT) {
            $message = "Your Password was changed Successfully.<br/>
                        **Username:**  Current Mail Id";
        } else {
            $message = "Your password was changed successfully.<br/>
                        <strong>Email:</strong> Current Mail Id<br/>
                        <strong>Password:</strong> Your chosen password.";
        } 
             
            $data['subject']='Fortgrid - Password Changed.';
            $data['to_addres']=$receiver_mail_id;
            $data['receiver_name']=optional($emp)->name ?? 'Unknown';
            $data['from_address']=$sender_mail_id;
            $data['sender_name']= $sender_name . '-' . $roleName;
            $data['message']=$message;
        // Store notification in the database
        DB::table('notification')->insert([
            'notify_type' =>$notifyType,
           'receiver_id' => $emp->id,
            'sender_id' => $senderId,
            'sender_name' => $sender_name . '-' . $roleName,
            'receiver_name' =>  optional($emp)->name ?? 'Unknown',
            'is_read' => config('global.notify_unread'),
            'subject' => 'Fortgrid - Password Changed.',
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
         $emp->notify(new NotifyInfoSendMail($notifyType, $data));
        }
    }
}


