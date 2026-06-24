<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\TaskNotificationMail;
use Illuminate\Support\Facades\Mail;


class SendTaskNotificationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $subject;
    protected $message;
    protected $regards;

    public function __construct($user, string $subject, string $message, string $regards)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->message = $message;
        $this->regards=$regards;
    }

    public function handle()
    {
        Mail::to($this->user->email)->send(new TaskNotificationMail($this->message, $this->subject,$this->regards));
    }
}
