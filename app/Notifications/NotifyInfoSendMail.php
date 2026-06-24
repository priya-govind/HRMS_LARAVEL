<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifyInfoSendMail extends Notification
{
    use Queueable;


    protected $notifyType;
    protected $data;


    public function __construct($notifyType, $data)
    {
       $this->notifyType = $notifyType;
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
// public function toDatabase($notifiable)
// {
//     return [
//         'team_type_name' => $this->teamType->team_typ_name,
//         'pm_id' => $this->pm->id,
//         'pm_name' => $this->pm->name,
//         'message' => 'You have been assigned a new team type. Please check your dashboard for details.',
//     ];
// }
    public function toMail($notifiable)
    {
        
            $plainMessage = preg_replace('/<a href=["\'](.*?)["\'].*?>.*?<\/a>/', '$1', $this->data['message']); 
            $plainMessage = str_replace('<br/>', "\n", $plainMessage); // Convert <br/> to new line
            $lines = explode("\n", strip_tags($plainMessage));
            // Ensure the correct URL format

            $mail = (new MailMessage)
                ->subject($this->data['subject'])
                ->greeting('Hi, ' . $this->data['receiver_name']);

            foreach ($lines as $line) {
                $mail->line(trim($line)); // Trim excess spaces for clean formatting
            }
            // Properly format the action link separately so it isn't mixed in the text output
            if($this->notifyType=='team_type'){
             $link = url('/team_types');
             $mail->action('Click Here to view Team Type Details', $link);
            }
           

            $mail->from($this->data['from_address']);

            return $mail;
        }
    }