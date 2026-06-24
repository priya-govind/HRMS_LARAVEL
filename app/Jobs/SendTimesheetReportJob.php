<?php

namespace App\Jobs;

use App\Mail\TimesheetReportMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTimesheetReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipients;
    protected $filePath;
    protected $fileName;

    public function __construct($recipients, $filePath, $fileName)
    {
        $this->recipients = $recipients;
        $this->filePath   = $filePath;
        $this->fileName   = $fileName;
    }

    public function handle()
    {
        foreach ($this->recipients as $email) {
            Mail::to($email)->send(new TimesheetReportMail($this->filePath, $this->fileName));
        }
    }
}