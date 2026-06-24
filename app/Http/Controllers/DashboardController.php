<?php

namespace App\Http\Controllers;
use App\Models\WorkingMode;
use App\Models\Attendance;
use App\Models\Leaveinfo;
use App\Models\Tasks;
use App\Models\PMTasks;
use App\Models\Projects;
use App\Models\Activitylog;
use App\Models\User;
use App\Models\TeamType;
use App\Models\TaskAssignEmp;
use App\Models\TaskStatusTeam;
use App\Models\BirthdayCalendar;
use App\Models\RaiseTicket;
use App\Models\Timesheet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        
          $work_mode=WorkingMode::where('mode_status',config('global.active_status'))
                            ->select('id','work_mode_name')
                            ->get(); 
                            
        $mgmt_role=config('global.task_approve_roles');
        //dd(session()->all());

        if(session('role_id')==config('global_roles.CEO') || in_array(session('role_id'),$mgmt_role)){
            return  $this->ceo_dashboard($work_mode); 
        }  else if(session('role_id')==config('global_roles.HR')){
            return  $this->hr_dashboard($work_mode); 
        } 
         else {
            return $this->employee_dashboard($work_mode);
        }
    }
   public function employee_dashboard($work_mode)
    {
        $employeeId = session('user_id');

        // Task counts for dashboard summary
        $taskCounts = TaskAssignEmp::join('tasks', 'tasks.id', '=', 'task_assign_emp.task_id')
            ->selectRaw("
                COUNT(IF(emp_task_status = 14, 1, NULL)) AS reopen_tasks,
                COUNT(IF(emp_task_status = 9, 1, NULL)) AS inprogress_tasks,
                COUNT(IF(emp_task_status = 11, 1, NULL)) AS completed_tasks
            ")
            ->whereNull('tasks.deleted_at')
            ->where('employee_id', $employeeId)
            ->first();

        // In-progress tasks with associated members
       $tasks = Tasks::whereHas('assignedNormalEmployees', function ($query) use ($employeeId) {
                        // Ensure the current user is assigned
                        $query->where('employee_id', $employeeId)
                            ->whereNotIn('emp_task_status', config('global.filter_progress_tasks'));
                    })
                    ->whereHas('assignedNormalEmployees', function ($query) use ($employeeId) {
                        // Ensure at least one other employee is also assigned
                        $query->where('employee_id', '!=', $employeeId);
                    })
                    ->with([
                        'assignedNormalEmployees' => function ($query) use ($employeeId) {
                            // Load only the other employees
                            $query->where('employee_id', '!=', $employeeId);
                        },
                        'assignedNormalEmployees.employee'
                    ])
                    ->get();
        $ticket_info= RaiseTicket::with('ticketStatus','TicketOwner','AssignedTicketMembers.user')->where('ticket_status','!=',config('global_task_status.Completed'))
                                ->orderBy('id', 'desc')
                                ->limit(5)->get();
        $loadChat=true;
        return view('dashboard', compact('work_mode', 'taskCounts', 'tasks','loadChat','ticket_info'));
    }
public function pm_dashboard($work_mode)
{
    $loadChat = true;
    // Config and session values
    $inProgressStatus = config('global_task_status.In Progress');
    $notstartStatus=config('global_task_status.Not Started');
    $completedStatus = config('global.completed_status');
    $reopenStatus = config('global.reopen_status');
    $activeStatus = config('global.active_status');
    $teamType = session('team_type');
    $teamId = session('team_id');
    $userId = session('user_id');

    // Base employee query
    $baseQuery = User::where('emp_status', $activeStatus)->whereHas('roles', function ($query) {
                            $query->whereNotIn('roles.id',config('global.mgmt_team')); // or use ->where('id', $roleId)
                        }) ->where('id','!=',session('user_id'));
    $tot_emp = $baseQuery->count();

    // Team employee count
    $tot_team_emp = (clone $baseQuery)
        ->when($teamType, fn($q) => $q->whereIn('team_type', $teamType))
        ->when(!$teamType, fn($q) => $q->whereHas('team_members', fn($q) => $q->where('team_id', $teamId)))
        ->count();

    // Team attendance
    $team_attend_info = Attendance::whereDate('chkinDate', today())
        ->where('emp_id', '!=', $userId)
        ->when($teamType, fn($q) => $q->whereHas('employee', fn($q) => $q->whereIn('team_type', $teamType)))
        ->when(!$teamType, fn($q) => $q->whereHas('employee.team_members', fn($q) => $q->where('team_id', $teamId)))
        ->selectRaw('COUNT(DISTINCT emp_id) as team_total_present')
        ->first();

    // Task count
    $totalTasks = Tasks::when($teamType, fn($q) => $q->whereRaw("FIND_IN_SET(?, team_typ_id)", [$teamType]))
        ->when(!$teamType, fn($q) => $q->whereHas('assignedEmployees', fn($q) => $q->where('team_id', $teamId)))
        ->count();

    // Task status breakdown
    $overallTask = TaskStatusTeam::whereIn('team_id', (array) $teamId)
        ->selectRaw("
            COUNT(CASE WHEN team_status = '{$inProgressStatus}' THEN 1 END) as inprogress_tasks,
            COUNT(CASE WHEN team_status = '{$completedStatus}' THEN 1 END) as completed_tasks,
            COUNT(CASE WHEN team_status = '{$reopenStatus}' THEN 1 END) as reopen_tasks,
             COUNT(CASE WHEN team_status = '{$notstartStatus}' THEN 1 END) as notstart_tasks
        ")
        ->first();

    // Attendance and leave
    $attend_info = Attendance::whereDate('chkinDate', today())
        ->selectRaw('COUNT(DISTINCT emp_id) as total_present')
        ->where('emp_id','!=',session('user_id'))
        ->first();
    // Percentages
    $present_percent = $attend_info->total_present > 0
        ? round(($attend_info->total_present / $tot_emp) * 100, 2)
        : 0;

    $totalTasks = PMTasks::count();
    $completedTasks = PMTasks::where('task_status', config('global.completed_status'))->count();
    $completionPercent = $totalTasks > 0
        ? round(($completedTasks / $totalTasks) * 100, 2)
        : 0;

    // Project completion stats
     $completionStats = Projects::with([
                                            'pm_tasks.assignedEmployees.employee:id,image',
                                        ])
                                ->where('proj_status', '!=', 8)
                                ->whereHas('pm_tasks', fn($q) =>
                                    $q->where('task_status', '!=', config('global_task_status.Not Started'))
                                )
                                ->get()
                                ->map(function ($project) {
                                    $allAssignments = $project->pm_tasks->flatMap(fn($task) =>
                                        $task->assignedEmployees ?? collect()
                                    );

                                    $totalAssignments = $allAssignments->count();
                                    $completedAssignments = $allAssignments->where('emp_task_status', config('global.completed_status'))->count();

                                    $completionPercentage = $totalAssignments > 0
                                        ? round(($completedAssignments / $totalAssignments) * 100, 2)
                                        : 0;
                                    if ($completionPercentage === 0) {
                                        return null;
                                    }

                                    $uniqueUsers = $allAssignments->pluck('user')->filter()->unique('id');
                                    $randomImages = $uniqueUsers->pluck('image')->shuffle()->take(3);
                                    $members_cnt = $uniqueUsers->count();

                                    $tag = match (true) {
                                        $completionPercentage < 50 => 'bg-danger',
                                        $completionPercentage < 75 => 'bg-warning',
                                        default => 'bg-success',
                                    };

                                    return [
                                        'project_id' => $project->id,
                                        'project_name' => $project->proj_name,
                                        'completion_percent' => $completionPercentage,
                                        'random_member_images' => $randomImages,
                                        'has_more_than_three_members' => $members_cnt > 3,
                                        'members_cnt' => $members_cnt,
                                        'tag' => $tag,
                                    ];
                                })
                                ->filter();
           

        // Recent activity
        $recent_activity = Activitylog::whereIn('log_name', config('global.activity_logs'))
            ->latest()
            ->get()
            ->shuffle()
            ->take(5)
            ->map(fn($log) => [
                'id' => $log->id,
                'description' => $log->description,
                'created_at' => $log->created_at->diffForHumans(),
                'act_tag' => $log->log_name === 'tasks' ? 'fa-tasks' : 'fa-users',
            ]);

        // Final dashboard data
        $data = [
            'present_emp' => $attend_info->total_present,
            'present_percent' => $present_percent,
            'total_emp' => $tot_emp,
            'tot_team' => $tot_team_emp,
            'present_team_emp' => $team_attend_info->team_total_present,
            'task_complete_cnt' => $completionPercent,
            'completed_tasks' => $overallTask->completed_tasks,
            'inprogress_tasks' => $overallTask->inprogress_tasks,
            'reopen_tasks' => $overallTask->reopen_tasks,
            'notstart_tasks' => $overallTask->notstart_tasks,
        ];

        //$teamTypes = TeamType::all();
    // Fetch timesheets with relationships
        $timesheetData = DB::table('pm_timesheets')
                ->join('users', 'pm_timesheets.emp_id', '=', 'users.id')
                ->join('projects', 'pm_timesheets.project_id', '=', 'projects.id')
                ->join('proj_modules', 'pm_timesheets.module_id', '=', 'proj_modules.id')
                ->select(
                    'users.name as employee',
                    'projects.proj_name as project_name',
                    'proj_modules.module_name as module_name',
                    'projects.proj_color as project_color',
                    DB::raw('ROUND(SUM(pm_timesheets.duration)/60,1) as total_hours')
                )
                ->whereNotNull('pm_timesheets.project_id')
                ->where('pm_timesheets.project_id', '<>', '')
                ->whereNotNull('pm_timesheets.module_id')
                ->where('pm_timesheets.module_id', '<>', '')
                ->groupBy('employee','project_name','module_name','proj_color')
                ->havingRaw('SUM(pm_timesheets.duration)/60 > 5') // only > 3 hrs
                ->orderBy('projects.order_by', 'asc')  
                ->get();
            $projects = [];
                foreach ($timesheetData as $row) {
                    if ($row->total_hours > 0) {
                        $projects[$row->project_name][$row->module_name][$row->employee] = [
                            "value" => $row->total_hours
                        ];
                    }
                }
                // All Projects
            // $projects["All Projects"] = [];
            //     foreach ($timesheetData as $row) {
            //         $projects["All Projects"][$row->module_name][$row->employee] = [
            //             "value" => $row->total_hours
            //         ];
            //     }

    //dd($allModulesChart);
    $LoadBarChart=true;

        return view('pm_dashboard', compact(
            'work_mode',
            'loadChat',
            'data',
            'completionStats',
            'recent_activity',
            'LoadBarChart',
            'projects'
            //'teamTypes'
        ));
}
public function ceo_dashboard($work_mode)
{
    $loadChat = true;
    // Total active employees
    $tot_emp = User::where('emp_status', config('global.active_status'))
                        ->whereHas('roles', function ($query) {
                            $query->whereNotIn('roles.id',config('global.role_without_attendance')); // or use ->where('id', $roleId)
                        })->count();

    // Attendance summary
    $attend_info = Attendance::selectRaw('
        COUNT(DISTINCT emp_id) as total_present,
        COUNT(DISTINCT CASE WHEN TIME(chkinDate) > "09:30:00" THEN emp_id END) as late_comers
    ')
    ->whereDate('chkinDate', today())
    ->first();

    // Leave summary
    $leave_info = Leaveinfo::selectRaw('
        COUNT(DISTINCT CASE WHEN leave_type = 1 THEN emp_id END) AS absent_emp,
        COUNT(DISTINCT CASE WHEN leave_type = 2 THEN emp_id END) AS permission_emp
    ')
    ->whereDate('from_dt', today())
    ->first();

    // Attendance percentage
    $present_percent = $tot_emp > 0
        ? round(($attend_info->total_present / $tot_emp) * 100, 2)
        : 0;

    // Active projects count
    $activeProjectsCount = PMTasks::whereNotIn('task_status', [config('global.completed_status')])
        ->distinct('project_id')
        ->count('project_id');

    // Task completion stats
    $totalTasks = PMTasks::count();
    $completedTasks = PMTasks::where('task_status', config('global.completed_status'))->count();
    $completionPercent = $totalTasks > 0
        ? round(($completedTasks / $totalTasks) * 100, 2)
        : 0;

            // Project completion breakdown
        $completionStats = Projects::with([
                                            'pm_tasks.assignedEmployees.employee:id,image',
                                        ])
                                ->where('proj_status', '!=', 8)
                                ->whereHas('pm_tasks', fn($q) =>
                                    $q->where('task_status', '!=', config('global_task_status.Not Started'))
                                )
                                ->get()
                                ->map(function ($project) {
                                    $allAssignments = $project->pm_tasks->flatMap(fn($task) =>
                                        $task->assignedEmployees ?? collect()
                                    );

                                    $totalAssignments = $allAssignments->count();
                                    $completedAssignments = $allAssignments->where('emp_task_status', config('global.completed_status'))->count();

                                    $completionPercentage = $totalAssignments > 0
                                        ? round(($completedAssignments / $totalAssignments) * 100, 2)
                                        : 0;
                                    if ($completionPercentage === 0) {
                                        return null;
                                    }

                                    $uniqueUsers = $allAssignments->pluck('user')->filter()->unique('id');
                                    $randomImages = $uniqueUsers->pluck('image')->shuffle()->take(3);
                                    $members_cnt = $uniqueUsers->count();

                                    $tag = match (true) {
                                        $completionPercentage < 50 => 'bg-danger',
                                        $completionPercentage < 75 => 'bg-warning',
                                        default => 'bg-success',
                                    };

                                    return [
                                        'project_id' => $project->id,
                                        'project_name' => $project->proj_name,
                                        'completion_percent' => $completionPercentage,
                                        'random_member_images' => $randomImages,
                                        'has_more_than_three_members' => $members_cnt > 3,
                                        'members_cnt' => $members_cnt,
                                        'tag' => $tag,
                                    ];
                                })
                                ->filter();
                                //echo 'continue here';
                                //dd($completionStats);
        // Recent activity logs
        $recent_activity = Activitylog::whereIn('log_name', config('global.activity_logs'))
            ->latest()
            ->take(20) // fetch more, then shuffle
            ->get()
            ->shuffle()
            ->take(5)
            ->map(function ($log) {
                $act_tag = $log->log_name === 'tasks' ? 'fa-tasks' : 'fa-users';
                return [
                    'id' => $log->id,
                    'description' => $log->description,
                    'created_at' => $log->created_at->diffForHumans(),
                    'act_tag' => $act_tag
                ];
            });

        // Final dashboard data
        $data = [
            'present_emp' => $attend_info->total_present,
            'late_emp' => $attend_info->late_comers,
            'absent' => $leave_info->absent_emp,
            'permission' => $leave_info->permission_emp,
            'present_percent' => $present_percent,
            'total_emp' => $tot_emp,
            'active_proj' => $activeProjectsCount,
            'task_complete_cnt' => $completionPercent,
        ];
$projects_col=Projects::all();
$employees = User::where('emp_status', config('global.active_status'))
    ->whereHas('roles', function ($query) {
        $query->whereNotIn('roles.id', config('global.monitor_employees_act'));
    })
    ->distinct()
    ->pluck('name','id');
       $timesheetData = DB::table('pm_timesheets')
            ->join('users', 'pm_timesheets.emp_id', '=', 'users.id')
            ->join('projects', 'pm_timesheets.project_id', '=', 'projects.id')
            ->join('proj_modules', 'pm_timesheets.module_id', '=', 'proj_modules.id')
            ->select(
                'users.name as employee',
                'projects.proj_name as project_name',
                'proj_modules.module_name as module_name',
                'projects.proj_color as project_color',
                DB::raw('ROUND(SUM(pm_timesheets.duration)/60,1) as total_hours')
            )
            ->whereNotNull('pm_timesheets.project_id')
            ->where('pm_timesheets.project_id', '<>', '')
            ->whereNotNull('pm_timesheets.module_id')
            ->where('pm_timesheets.module_id', '<>', '')
            ->groupBy('employee','project_name','module_name','proj_color')
            ->havingRaw('SUM(pm_timesheets.duration)/60 > 5') // only > 3 hrs
             ->orderBy('projects.order_by', 'asc') 
            ->get();
        $projects = [];
            foreach ($timesheetData as $row) {
                 if ($row->total_hours > 0) {
                        $projects[$row->project_name][$row->module_name][$row->employee] = [
                            "value" => $row->total_hours
                        ];
                    }
            }
            // All Projects
        // $projects["All Projects"] = [];
        //     foreach ($timesheetData as $row) {
        //         $projects["All Projects"][$row->module_name][$row->employee] = [
        //             "value" => $row->total_hours
        //         ];
        //     }

//dd($allModulesChart);
        $LoadBarChart=true;
        $LoadDatatables=true;
        //$teamTypes = TeamType::all();
        $ticket_info= RaiseTicket::with('ticketStatus','TicketOwner','AssignedTicketMembers.user')->where('ticket_status','!=',config('global_task_status.Completed'))
                                ->orderBy('id', 'desc')
                                ->limit(5)->get();
           $currDate=Carbon::today()->format('Y-m-d');
                    $leaves_info = Leaveinfo::whereDate('from_dt', '>=', $currDate)
                                        ->orderBy('id', 'desc')
                                        ->orderBy('leave_status', 'asc')->limit(5)->get();
        return view('ceo_dashboard', compact(
            'loadChat',
            'data',
            'completionStats',
            'recent_activity',
            //'teamTypes',
            'ticket_info',
            'leaves_info',
            'LoadBarChart',
            'work_mode',
            'projects',
            'employees',
            'projects_col',
            'LoadDatatables'
        ));
}
public function hr_dashboard($work_mode)
{
    $loadChat = true;
    // Total active employees
    $tot_emp = User::where('emp_status', config('global.active_status'))
                        ->whereHas('roles', function ($query) {
                            $query->whereNotIn('roles.id',config('global.mgmt_team')); // or use ->where('id', $roleId)
                        })->count();

    // Attendance summary
    $attend_info = Attendance::selectRaw('
        COUNT(DISTINCT emp_id) as total_present,
        COUNT(DISTINCT CASE WHEN TIME(chkinDate) > "09:30:00" THEN emp_id END) as late_comers
    ')
    ->whereDate('chkinDate', today())
    ->first();

    // Leave summary
    $leave_info = Leaveinfo::selectRaw('
        COUNT(DISTINCT CASE WHEN leave_type = 1 THEN emp_id END) AS absent_emp,
        COUNT(DISTINCT CASE WHEN leave_type = 2 THEN emp_id END) AS permission_emp
    ')
    ->whereDate('from_dt', today())
    ->first();

    // Attendance percentage
    $present_percent = $tot_emp > 0
        ? round(($attend_info->total_present / $tot_emp) * 100, 2)
        : 0;

        // Final dashboard data
        $data = [
            'present_emp' => $attend_info->total_present,
            'late_emp' => $attend_info->late_comers,
            'absent' => $leave_info->absent_emp,
            'permission' => $leave_info->permission_emp,
            'present_percent' => $present_percent,
            'total_emp' => $tot_emp,
            
        ];
/**Graph for Attendance */
     
$teamTypes = TeamType::all();
    $startDate = Carbon::now()->subDays(6)->startOfDay();
    $endDate = Carbon::now()->endOfDay();

    // Get attendance counts per team per day
    $attendance = \App\Models\Attendance::selectRaw('
            DATE(chkinDate) as date,
            users.team_type,
            COUNT(DISTINCT attendance.emp_id) as present_count
        ')
        ->join('users', 'attendance.emp_id', '=', 'users.id')
        ->whereBetween('chkinDate', [$startDate, $endDate])
        ->groupBy('date', 'users.team_type')
        ->get()
        ->groupBy('team_type');

    // Get total members per team
    $teamCounts = \App\Models\User::select('team_type', DB::raw('COUNT(*) as total'))
        ->groupBy('team_type')
        ->pluck('total', 'team_type');

    // Generate last 7 dates
    $dates = collect();
    for ($i = 6; $i >= 0; $i--) {
        $dates->push(Carbon::now()->subDays($i)->format('Y-m-d'));
    }
/**Graph for Attendance */

        /**Leave and Permission Request Info */
             $currDate=Carbon::today()->format('Y-m-d');
                    $leaves_info = Leaveinfo::whereDate('from_dt', '>=', $currDate)
                                        ->orderBy('id', 'desc')
                                        ->orderBy('leave_status', 'asc')->limit(5)->get();


        /***Upcoming Birthdays */
        $today = Carbon::today();
        $currentMonth = $today->month;
        $currentDay = $today->day;

        $upcome_birth = BirthdayCalendar::whereMonth('birth_date', $currentMonth)
            ->whereDay('birth_date', '>=', $currentDay)
            ->orderByRaw('DAY(birth_date)')
            ->get();


        return view('hr_dashboard', compact(
            'loadChat',
            'data',
            'teamTypes',
            'leaves_info',
            'upcome_birth',
             'attendance', 
             'teamCounts', 
             'dates',
             'work_mode'
        ));
}

    public function getTaskStatusData($id)
    {
        $statusConfig = config('global_task_status'); 
        $statusLabels = array_flip($statusConfig);

        $tasks = Tasks::whereRaw("FIND_IN_SET(?, team_typ_id)", [$id])
            ->select('task_status', DB::raw('count(*) as total'))
            ->groupBy('task_status')
            ->get();

        $data = [];
        foreach ($statusLabels as $code => $label) {
            $count = $tasks->firstWhere('task_status', $code)?->total ?? 0;
            $data[] = ['label' => $label, 'count' => $count];
        }
        return response()->json($data);
    }
public function getOverallStatusComparison(){
    $statusLabels = array_flip(config('global_task_status')); // [8 => 'Not Started', ...]

    $data = [];

    foreach ($statusLabels as $statusCode => $statusName) {
        $teamCount = DB::table('task_status_team')
            ->where('team_status', $statusCode)
            ->count();

        $empCount = DB::table('task_assign_emp')
            ->where('emp_task_status', $statusCode)
            ->count();

        $data[] = [
            'status' => $statusName,
            'Team' => $teamCount,
            //'Employee' => $empCount
        ];
    }

    return response()->json($data);
}

}
