<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendPMTaskNotificationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $assignedMembers;
    protected $subject;
    protected $mailMessage;
    protected $senderMeta;
    protected $show_team;
    protected $receiver_ids;

    public function __construct(array $assignedMembers, string $subject, string $mailMessage, array $senderMeta,bool $show_team,array $receiver_ids)
    {
        $this->assignedMembers = $assignedMembers;
        $this->subject         = $subject;
        $this->mailMessage     = $mailMessage;
        $this->senderMeta      = $senderMeta;
        $this->show_team       = $show_team;
        $this->receiver_ids    =$receiver_ids;
    }
public function handle(){
    // Force sequential keys
    //$emails = array_values(array_unique(array_map(fn($user) => $user['email'], $this->receiver_ids)));

    // \Log::info('SendPMTaskNotificationMail dispatched', [
    //     'subject'    => $this->subject,
    //     'recipients' => $emails
    // ]);
    \Log::info('Raw receiver_ids', $this->receiver_ids);

    // Flatten emails and log them
    $emails = array_values(array_unique(array_map(fn($user) => $user['email'], $this->receiver_ids)));
    \Log::info('Flattened emails', $emails);


    $data = [
        'mailMessage'     => $this->mailMessage,
        'subject'         => $this->subject,
        'sender'          => $this->senderMeta,
        'assignedMembers' => $this->assignedMembers,
        'show_team'       => $this->show_team,
        'receiver_ids'    => $this->receiver_ids,
    ];
    Mail::send('emails.pm_task_notification', $data, function ($mail) use ($emails) {
        $mail->to($emails)
             ->subject($this->subject)
             ->from($this->senderMeta['email'], $this->senderMeta['name']);
    });
}


}