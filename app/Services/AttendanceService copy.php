<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Models\TeamMembers;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceService
{
        /**
     * Get today's attendance for a single user.
     */
    public function getTodayAttendance(int $userId)
    {
        return Attendance::where('emp_id', $userId)
            ->whereDate('chkinDate', Carbon::today())
            ->first();
    }
    public function getTodayAllAttendance()
    {
        return Attendance::whereDate('chkinDate', Carbon::today())
            ->get();
    }
        public function getAllAttendanceByDate(string $date)
    {
        return Attendance::whereDate('chkinDate', $date)
            ->get();
    }
    /**
     * Get attendance for a single user by date.
     */
    public function getAttendanceByDate(int $userId, string $date)
    {
        return Attendance::where('emp_id', $userId)
            ->whereDate('chkinDate', $date)
            ->first();
    }

    /**
     * Get today's attendance for all team members under a manager/lead.
     */
    public function getTeamTodayAttendance(int $managerId)
    {
        $teamIds = $this->getTeamMemberIds($managerId);

        return Attendance::whereIn('emp_id', $teamIds)
            ->whereDate('chkinDate', Carbon::today())
            ->get();
    }

    /**
     * Get attendance for team members by date.
     */
    public function getTeamAttendanceByDate(int $managerId, string $date)
    {
        $teamIds = $this->getTeamMemberIds($managerId);

        return Attendance::whereIn('emp_id', $teamIds)
            ->whereDate('chkinDate', $date)
            ->get();
    }

    /**
     * Helper: get IDs of team members for a manager/lead.
     * Adjust this logic based on your org structure.
     */
    protected function getTeamMemberIds(int $managerId): array
    {
        $emp_ids = [];
        $members = TeamMembers::whereIn('team_id', session('team_id'))->get();
            foreach ($members as $member) {
                $emp_ids[] = $member->emp_id;
            }
            return $emp_ids;
    }

    protected function handleAttendanceSelfPastFetch()
    {
        $userId = Auth::id();
        $input = request()->input('date'); // expects dd/mm/yy

        try {
            $date = \Carbon\Carbon::createFromFormat('d/m/Y', $input)->toDateString();
        } catch (\Exception $e) {
            return ['reply' => 'Invalid date format. Please use dd/mm/yyyy.'];
        }

        $attendanceService = new AttendanceService();
        $row = $attendanceService->getAttendanceByDate($userId, $date);

        if ($row) {
            return [
                'reply' => "Your attendance on {$input}:",
                'data'  => [[
                    'Date'        => $date,
                    'CheckIn'     => \Carbon\Carbon::parse($row->chkinDate)->format('H:i:s'),
                    'CheckOut'    => $row->chkoutDate ? \Carbon\Carbon::parse($row->chkoutDate)->format('H:i:s') : 'Still Checked In',
                    'WorkedHours' => $row->work_duration ?? 'still working',
                ]],
            ];
        }

        return [
            'reply' => "Absent on {$input}.",
            'data'  => [[ 'Date' => $date, 'Status' => 'Absent' ]],
        ];
    }

    protected function handleAttendanceTeamPastFetch()
    {
        $userId = Auth::id();
        $input = request()->input('date'); // expects dd/mm/yy

        try {
            $date = \Carbon\Carbon::createFromFormat('d/m/Y', $input)->toDateString();
        } catch (\Exception $e) {
            return ['reply' => 'Invalid date format. Please use dd/mm/yyyy.'];
        }

        $attendanceService = new AttendanceService();
        $rows = $attendanceService->getTeamAttendanceByDate($userId, $date);

        if ($rows && count($rows) > 0) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'Member'      => $row->member_name ?? $row->user_name ?? 'Unknown',
                    'Date'        => $date,
                    'CheckIn'     => \Carbon\Carbon::parse($row->chkinDate)->format('H:i:s'),
                    'CheckOut'    => $row->chkoutDate ? \Carbon\Carbon::parse($row->chkoutDate)->format('H:i:s') : 'Still Checked In',
                    'WorkedHours' => $row->work_duration ?? 'still working',
                ];
            }
            return ['reply' => "Team attendance on {$input}:", 'data' => $data];
        }

        return [
            'reply' => "No records found for {$input}. Team absent.",
            'data'  => [[ 'Date' => $date, 'Status' => 'Absent' ]],
        ];
    }
    /**public function getTodayAttendance($userId)
    {
        return Attendance::whereDate('chkinDate', today())
        ->where('emp_id', $userId) ->first();
    }

    public function getAttendanceByDate($userId, $date)
    {
        return Attendance::where('emp_id', $userId)
            ->whereDate('chkinDate', $date)
            ->first();
    }*/
}