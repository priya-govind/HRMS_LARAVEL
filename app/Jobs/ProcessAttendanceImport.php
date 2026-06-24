<?php

namespace App\Jobs;

use App\Imports\AttendanceImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ProcessAttendanceImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rows;
    protected $workMode;

    public function __construct(Collection $rows, $workMode)
    {
        $this->rows = $rows;
        $this->workMode = $workMode;
    }

    public function handle()
    {
        $importer = new AttendanceImport($this->workMode);
        $importer->collection($this->rows);
    }
}