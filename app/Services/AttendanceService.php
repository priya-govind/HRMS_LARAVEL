<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\TeamMembers;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceService
{
    /** Get today's attendance for a single user */
    public function getTodayAttendance(int $userId)
    {
        $rows = Attendance::where('emp_id', $userId)
            ->whereDate('chkinDate', Carbon::today())
            ->get();

        return $this->formatAttendanceResponse($rows, 'Your attendance today:');
    }

    /** Get today's attendance for all users */
    public function getTodayAllAttendance()
    {
        $rows = Attendance::whereDate('chkinDate', Carbon::today())->get();
        return $this->formatAttendanceResponse($rows, 'All attendance today:');
    }

    /** Get attendance for a single user by date */
    public function getAttendanceByDate(int $userId, string $date)
    {
        $rows = Attendance::where('emp_id', $userId)
            ->whereDate('chkinDate', Carbon::createFromFormat('d/m/Y', $date)
)
            ->get();

        return $this->formatAttendanceResponse($rows, "Your attendance on {$date}:");
    }

    /** Get today's attendance for all team members under a manager */
    public function getTeamTodayAttendance(int $managerId)
    {
        $teamIds = $this->getTeamMemberIds($managerId);

        $rows = Attendance::whereIn('emp_id', $teamIds)
            ->whereDate('chkinDate', Carbon::today())
            ->get();

        return $this->formatAttendanceResponse($rows, 'Team attendance today:');
    }

    /** Get attendance for team members by date */
    public function getTeamAttendanceByDate(int $managerId, string $date)
    {
        $teamIds = $this->getTeamMemberIds($managerId);

        $rows = Attendance::whereIn('emp_id', $teamIds)
            ->whereDate('chkinDate', Carbon::parse($date))
            ->get();

        return $this->formatAttendanceResponse($rows, "Team attendance on {$date}:");
    }

    /** Helper: get IDs of team members for a manager/lead */
    protected function getTeamMemberIds(int $managerId): array
    {
        return TeamMembers::whereIn('team_id', session('team_id'))
            ->pluck('emp_id')
            ->toArray();
    }

    /** Format attendance rows into JSON for frontend */
    protected function formatAttendanceResponse($rows, string $title)
    {
        if (!$rows || $rows->isEmpty()) {
            return ['reply' => 'No attendance record found.'];
        }

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'Name'        => $row->user->name ?? 'Unknown',
                'Date'        => Carbon::parse($row->chkinDate)->format('d M,Y'),
                'Check In'    => Carbon::parse($row->chkinDate)->format('H:i A'),
                'Check Out'   => $row->chkoutDate 
                                  ? Carbon::parse($row->chkoutDate)->format('H:i A') 
                                  : 'Still Checked In',
                'Worked Hours'=> $row->work_duration ?? 'still working',
            ];
        }

        return ['reply' => $title, 'data' => $data];
    }

     // 🔹 Self past range
    public function getAttendanceByDateRange(int $userId, string $fromDate, string $toDate){
        try {
            $from = Carbon::createFromFormat('d/m/Y', $fromDate)->startOfDay();
            $to   = Carbon::createFromFormat('d/m/Y', $toDate)->endOfDay();
        } catch (\Exception $e) {
            return ['reply' => 'Invalid date format. Please use dd/mm/yyyy.'];
        }

        if ($from->gt($to)) {
            return ['reply' => 'Start date must be before end date.'];
        }
$rows = Attendance::where('emp_id', $userId)
    ->whereBetween('chkinDate', [$from, $to]);

dd($rows->toSql(), $rows->getBindings());

        $rows = Attendance::where('emp_id', $userId)
            ->whereBetween('chkinDate', [$from, $to])
            ->get();

        return $this->formatAttendanceResponse(
            $rows,
            "Your attendance from {$fromDate} to {$toDate}:"
        );
    }
    // 🔹 Team past range
    public function getTeamAttendanceByDateRange(int $managerId, string $fromDate, string $toDate){
        try {
            $from = Carbon::createFromFormat('d/m/Y', $fromDate)->startOfDay();
            $to   = Carbon::createFromFormat('d/m/Y', $toDate)->endOfDay();
        } catch (\Exception $e) {
            return ['reply' => 'Invalid date format. Please use dd/mm/yyyy.'];
        }

        if ($from->gt($to)) {
            return ['reply' => 'Start date must be before end date.'];
        }

        $teamIds = $this->getTeamMemberIds($managerId);

        $rows = Attendance::whereIn('emp_id', $teamIds)
            ->whereBetween('chkinDate', [$from, $to])
            ->get();

        return $this->formatAttendanceResponse(
            $rows,
            "Team attendance from {$fromDate} to {$toDate}:"
        );
    }
    // 🔹 All users past range
    public function getAllAttendanceByDateRange(string $fromDate, string $toDate)
    {
        try {
            $from = Carbon::createFromFormat('d/m/Y', $fromDate)->toDateString();
            $to   = Carbon::createFromFormat('d/m/Y', $toDate)->toDateString();
        } catch (\Exception $e) {
            return ['reply' => 'Invalid date format. Please use dd/mm/yyyy.'];
        }

        if ($from > $to) {
            return ['reply' => 'Start date must be before end date.'];
        }

        $rows = Attendance::whereBetween('chkinDate', [$from, $to])->get();

        return $this->formatAttendanceResponse($rows, "Attendance from {$fromDate} to {$toDate}:");
    }


}