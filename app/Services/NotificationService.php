<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendTaskAssignmentNotification;
use App\Jobs\SendTaskStatusNotification;

class NotificationService
{
    public static function notifyMultipleUsers(
    array $allEmpIds,
    $entity,
    string $notifyType,
    string $subject,
    string $introLine,
    array $pmIds,         
    int $actorUserId
)
 {
        if (empty($allEmpIds)) return;

        $assignedEmpIds = array_keys(array_filter($allEmpIds, fn($v) => $v == 0));
        $reportingEmpIds = array_keys(array_filter($allEmpIds, fn($v) => $v == 1));

        $members = DB::table('task_assign_emp')
            ->whereIn('employee_id', array_merge($assignedEmpIds, $reportingEmpIds))
            ->join('users', 'users.id', '=', 'task_assign_emp.employee_id')
            ->leftJoin('teams', 'teams.id', '=', 'task_assign_emp.team_id')
            ->leftJoin('roles_user', 'roles_user.user_id', '=', 'users.id')
            ->leftJoin('roles', 'roles.id', '=', 'roles_user.roles_id')
            ->select('users.id as user_id', 'users.name', 'teams.team_name', 'roles.role_name')
            ->get()
            ->map(fn($u) => (array) $u)
            ->keyBy('user_id')
            ->toArray();

        $assignedView = collect($assignedEmpIds)
            ->map(fn($id) => $members[$id] ?? null)
            ->filter()
            ->values()
            ->toArray();

        $reportingView = collect($reportingEmpIds)
            ->map(fn($id) => $members[$id] ?? null)
            ->filter()
            ->values()
            ->toArray();

        $senderMeta = self::getSenderMeta();

        foreach ($allEmpIds as $empId => $roleFlag) {
            $user = User::find($empId);
            if (!$user) continue;

            $isControl = $roleFlag == 1;
            $isSelfPM = in_array($empId, $pmIds) && session('user_id') == $empId;
            $senderMeta = $isSelfPM
                                ? config('global.admin') // This likely lacks a 'role' key
                                : [
                                    'id' => session('user_id'),
                                    'name' => optional(User::find(session('user_id')))->name,
                                    'email' => optional(User::find(session('user_id')))->email,
                                    'role' => optional(optional(User::find(session('user_id')))->role)->name ?? 'User',
                                ];
           // logger()->info('Dispatching for', ['emp_id' => $empId, 'isSelfPM' => $isSelfPM]);

                        dispatch(new SendTaskAssignmentNotification(
                        $user,
                        $senderMeta,
                        $subject,
                        $introLine,
                    ));

        }
        
    }


 public static function notifyStatusTeam(
    array $allEmpIds,
    $entity,
    string $notifyType,
    string $subject,
    string $introLine,
    array $pmIds,         
    int $actorUserId,
    string  $teamName,
    string  $status,
     array $team_ids,
)
 {
        if (empty($allEmpIds)) return;

        $reportingEmpIds = array_keys(array_filter($allEmpIds, fn($v) => $v == 1));

        $reportingView = collect($reportingEmpIds)
            ->map(fn($id) => $members[$id] ?? null)
            ->filter()
            ->values()
            ->toArray();

        $senderMeta = self::getSenderMeta();

        foreach ($allEmpIds as $empId => $roleFlag) {
            $user = User::find($empId);
            if (!$user) continue;

            $isControl = $roleFlag == 1;
            $isSelfPM = in_array($empId, $pmIds) && session('user_id') == $empId;
            $isApprovalWaiting = $status == config('global.approval_waiting_status');

                $senderMeta = $isApprovalWaiting || $isSelfPM
                    ? config('global.admin')
                    : [
                        'id' => session('user_id'),
                        'name' => optional($user = User::find(session('user_id')))->name,
                        'email' => optional($user)->email,
                        'role' => optional($user->role)->name ?? 'User',
                    ];
            //logger()->info('Dispatching for', ['emp_id' => $empId, 'isSelfPM' => $isSelfPM]);

                        dispatch(new SendTaskStatusNotification(
                        $user,
                        $entity,
                        $reportingView,
                        $isControl,
                        $senderMeta,
                        $subject,
                        $introLine,
                        $notifyType,
                        $teamName,
                        $status,
                        $team_ids
                    ));

        }
        
    }

    private static function getSenderMeta(): array
    {
        $senderId = session('user_id') ?? config('global.superadmin_id');
        $sender = User::find($senderId);

        return [
            'id' => $senderId,
            'name' => session('user_name') ?? optional($sender)->name,
            'role' => session('role_name') ?? optional($sender)->roles->pluck('role_name')->first(),
        ];
    }
}