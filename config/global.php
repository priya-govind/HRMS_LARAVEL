<?php 

return [
    'load_category' => 8,
    'load_permissions' => 9,
    'load_roles' => 10,
    'load_users' =>11,
    //superad min role id
    'superadmin'=>17,
    'superadmin_id'=>1,
    /**Task Status */
    //for fetching status info from project_status table
    'task_set_status' =>1,
    'emp_set_status' =>1,
    'proj_set_status' =>1,
    'approval_waiting_status' =>13,
    'completed_status' =>11,
    'reopen_status'=> 14,
    /**Task Roles */
    /**Management Team */
    'mgmt_team' => [1,2,17],
    'role_without_attendance' => [1,2,17],
    /**PM */
    'first_level_role' => [3],
    'active_status_all'=> [9, 10, 13, 14],
    /**PL,TL */
    'restriction_free_roles' => [1,2,3,5,17],
    'monitor_employees_act' =>[1,2,3,4,5,17],
    'task_monitor_roles' => [4,5],
    'task_approve_roles'=>[3,4,5],
    'all_in_all_access' =>[3,17],
    'filter_progress_tasks' => [8,9],
    'task_status_badges' => [
        8  => ['label' => 'Not Started', 'class' => 'badge-primary'],
        9  => ['label' => 'In Progress', 'class' => 'badge-info'],
        10  => ['label' => 'Not Started', 'class' => 'badge-secondary'],
        11 => ['label' => 'Completed', 'class' => 'badge-success'],
        13 => ['label' => 'Waiting of Approval', 'class' => 'badge-warning'],
        14 => ['label' => 'Re open', 'class' => 'badge-danger'],
    ],
    /**Employee Status */
    'active_status'=>1,
    'inactive_status'=>0,
    /**Team Ctrller */
    'ctrl_status' =>1,
    'reopen_pending'=>0,
    'in_progress'=> 9,
    'not_started' =>8,
    'notify_read'=>0,
    'notify_unread'=>1,
    /**Leave Status */
    'leave_approval_pending' =>0,
    'leave_approved' =>1,
    'leave_rejected' =>2,
    'leave_cancelled' =>3,
    'leave_type_leave' => 1,
    'leave_type_permission' =>2,
     'activity_logs' => ['tasks', 'Teams'],
 ];


?>