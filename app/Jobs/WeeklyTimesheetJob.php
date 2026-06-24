<?php

namespace App\Jobs;

use App\Models\TeamType;
use App\Models\Timesheet;
use App\Exports\TimesheetExport;
use App\Mail\TimesheetZipMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class WeeklyTimesheetJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle()
    {
        $teamTypes = TeamType::with(['reportingPerson', 'teams.teamMembers.user'])->get();
        foreach ($teamTypes as $teamType) {
            $files = [];
            $noDataEmployees = [];   // <-- initialize here

            foreach ($teamType->teams as $team) {
                foreach ($team->teamMembers as $member) {
                    $user = $member->user;
                    if (!$user) continue;

                    // Check if employee has timesheet entries for last week
                    $hasData = Timesheet::where('emp_id', $user->id)
                        ->whereBetween('create_dt', [
                            now()->subWeek()->startOfWeek()->format('Y-m-d'),
                            now()->subWeek()->endOfWeek()->format('Y-m-d')
                        ])
                        ->exists();

                    if (!$hasData) {
                        $noDataEmployees[] = $user->name . ' (' . $user->email . ')';
                        continue; // skip Excel generation
                    }

                    $filename = "timesheet_{$user->name}_" . now()->format('Y-m-d') . ".xlsx";
                    $path = "timesheet_history/{$filename}";

                    Excel::store(
                        new TimesheetExport(
                            null,
                            now()->subWeek()->startOfWeek()->format('Y-m-d'),
                            now()->subWeek()->endOfWeek()->format('Y-m-d'),
                            $user->id,
                            $user->name
                        ),
                        $path,
                        'public'
                    );

                    $files[] = Storage::disk('public')->path($path);
                }
            }

            // Create ZIP in public disk
            //$zipFilename = "{$teamType->team_typ_name}_timesheet-" . now()->format('Y-m-d') . ".zip";
            $zipFilename = str_replace(' ', '_', "{$teamType->team_typ_name}_timesheet-" . now()->format('Y-m-d') . ".zip");

            $zipPath = Storage::disk('public')->path("timesheet_history/{$zipFilename}");

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();
            }

            // Send email with ZIP attached + no-data list
            if (!empty($files) &&  $teamType->reportingPerson && $teamType->reportingPerson->email) {
                Log::info("ZIP exists? " . (file_exists($zipPath) ? 'yes' : 'no') . " at $zipPath");
                Mail::to($teamType->reportingPerson->email)
                    ->send(new TimesheetZipMail($zipPath, $noDataEmployees));
                     sleep(1);
            }

            // Cleanup Excel files only (keep ZIP for download)
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}