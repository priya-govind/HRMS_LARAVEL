<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskNotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $messageContent;
    public $subjectLine;
    public $regards;

    /**
     * Create a new message instance.
     */
    public function __construct(string $messageContent, string $subjectLine, string $regards)
    {
        $this->messageContent = $messageContent. '<br><br>Regards,<br>'.$regards;
        $this->subjectLine = $subjectLine;
    }

     public function build()
    {
        return $this->subject($this->subjectLine)
                    ->html($this->messageContent); // Optional: use a markdown or view instead
    }
}
