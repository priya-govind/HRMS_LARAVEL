<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TimesheetZipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $zipPath;
     public $noDataEmployees;

    /**
     * Create a new message instance.
     */
    public function __construct($zipPath,$noDataEmployees)
    {
        $this->zipPath = $zipPath;
         $this->noDataEmployees = $noDataEmployees;
    }

    /**
     * Build the message.
     */
     public function build()
    {
        $mail = $this->subject('Weekly Timesheet Reports')
                     ->view('emails.timesheet')
                     ->with(['noDataEmployees' => $this->noDataEmployees]);

        if ($this->zipPath && file_exists($this->zipPath)) {
            $mail->attach($this->zipPath, [
                'as' => basename($this->zipPath),
                'mime' => 'application/zip',
            ]);
        }

        return $mail;
    }

}