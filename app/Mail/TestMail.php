<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;


class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

public function build()
    {
       $path= response()->file(resource_path('views/emails/test.blade.php'));

        return $this->from('priya.n@fortgrid.in')
                    ->cc('priya.n.e.srit@gmail.com') // Add CC here
                    ->subject('Test Email')
                    ->view($path, ['notification' => $this->notification]);
 // Ensure you have an email view
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
