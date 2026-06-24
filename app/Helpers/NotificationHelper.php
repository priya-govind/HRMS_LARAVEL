<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class NotificationHelper
{
    public static function getAllTaskRelatedUsers(int $taskId): array
    {
        $empMap = DB::table('task_assign_emp')
            ->where('task_id', $taskId)
            ->pluck('ctrl_status', 'employee_id')
            ->toArray();

        $pmIds = DB::table('task_assign_emp')
            ->where('task_assign_emp.task_id', $taskId)
            ->join('teams', 'teams.id', '=', 'task_assign_emp.team_id')
            ->join('team_types', 'team_types.id', '=', 'teams.team_type')
            ->pluck('team_types.pm_id')
            ->unique()
            ->filter()
            ->toArray();

        foreach ($pmIds as $pmId) {
            $empMap[$pmId] = 1; // Treat PMs as reporting members
        }

        return $empMap;
    }
}