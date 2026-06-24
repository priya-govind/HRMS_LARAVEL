<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendanceImport;

class ImportPunchCardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;
    protected $workMode;

    public function __construct($file, $workMode)
    {
        $this->file = $file;
        $this->workMode = $workMode;
    }

    public function handle()
    {
        Excel::import(new AttendanceImport($this->workMode), $this->file);
    }
}