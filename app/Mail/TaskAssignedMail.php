<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
public $title;
public $assigned;
public $reporting;
public $excludeSelf;

public function __construct($user, $title, $assigned, $reporting, $excludeSelf = false)
{
    $this->user = $user;
    $this->title = $title;
    $this->assigned = $assigned;
    $this->reporting = $reporting;
    $this->excludeSelf = $excludeSelf;
}
public function build()
{
    return $this->subject($this->title)
                ->markdown('emails.task_notification');
}


}
