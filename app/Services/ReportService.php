<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Timesheet;
use App\Models\Leave;

class ReportService
{
    public function getAttendanceReport($userId)
    {
        return Attendance::where('user_id', $userId)->orderBy('date', 'desc')->get();
    }

    public function getTimesheetReport($userId)
    {
        return Timesheet::where('user_id', $userId)->orderBy('date', 'desc')->get();
    }

    public function getLeaveReport($userId)
    {
        return Leave::where('user_id', $userId)->orderBy('start_date', 'desc')->get();
    }
}