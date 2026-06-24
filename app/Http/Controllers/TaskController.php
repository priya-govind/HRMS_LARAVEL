<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Helpers\ActivityHelper;

use App\Models\ProjectType;
use App\Models\TeamType;
use App\Models\Tasks;
use App\Models\Projects;
use App\Models\Teams;
use App\Models\TaskStatus;
use App\Models\TaskAssignEmp;
use App\Models\TaskAssignTeam;
use App\Models\ProjectStatus;
use App\Models\TeamMembers;
use App\Models\ReopenedTask;
use App\Models\TaskStatusTeam;
use App\Models\PMTasks;
use App\Models\PMTasksAssign;
use App\Models\TaskFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Jobs\SendTaskNotificationMail;
use App\Jobs\SendPMTaskNotificationMail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;
//"HRMS background mail & notify sync"


class TaskController extends Controller
{
    public function manage_tasks(Request $request,$current_status=''){
        $path = request()->path();
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        // print_r(session('team_id'));
        // die;
        $mgmt_role=config('global.task_approve_roles');
        $all_in_allaccess=config('global.all_in_all_access');
        if((session('role_id')==config('global_roles.CEO') || in_array(session('role_id'),config('global.task_approve_roles'))) && (Str::contains($path, 'filter/active_projects'))){
             return  $this->active_proj_tasks($request); 
        } else if(in_array(session('role_id'),$all_in_allaccess)){
            /**For PM */
            return  $this->ViewMainTasks($request,$current_status); 
        }else if(in_array(session('role_id'),$mgmt_role)){
            /**For TL and PL */
             return  $this->ViewMainTasksLevel($request,$current_status);
        } else {
            /**For all Employees */
            return $this->ViewIndividualTasks($request,$current_status);
        }  
    }
    public function active_proj_tasks(Request $request,$proj_id=''){
        $LoadDatatables=true; 
    
        $active_proj_info=PMTasksAssign::with('task','task.project','employee','empStatus')
                                    ->join('pm_tasks', 'pm_tasks.id', '=', 'pm_task_assign_emp.task_id');
                                if(isset($proj_id) && !empty($proj_id)){
                                    //$proj_id=Crypt::decrypt($proj_id);
                                    $active_proj_info->where('pm_tasks.project_id',$proj_id);
                                }
              
                 $active_proj_info = $active_proj_info->get()
                                    ->map(function ($project) {
                                        return [
                                        'emp_name' => $project->employee->name,
                                        'emp_task_name' => $project->task_name ?: 'Task Name not Yet Assigned',
                                        'task_status'=>  optional($project->empStatus)->proj_status_name,
                                         'project_name'  => optional($project->task->project)->proj_name, //
                                        ];
                                     });        
                $projectTitle = '';
                if(isset($proj_id) && !empty($proj_id)){
                    $projectTitle = $active_proj_info->first()['project_name'] ?? '';
                }
                    
                  // dd($active_proj_info);                
                 if ($request->ajax()) {
      
            return DataTables::of($active_proj_info)
                ->addIndexColumn()
                ->make(true);
        }                             
        return view('tasks.view_tasks_ceo', compact('proj_id','LoadDatatables','projectTitle'));
    }
      /**For CEO and PM */
    public function ViewMainTasks(Request $request)
    {
        $LoadDatatables=true; 
        $project_status= ProjectStatus::where('task_set_status',config('global.task_set_status'))->get();
        $task_status=ProjectStatus::where('emp_set_status',config('global.emp_set_status'))->get();
        $view_permit=PermissionHelper::checkPermission('global.categories',$this->view_perm);
        $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
        $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);
        if ($request->ajax()) {
            $projects = Tasks::with(['project', 'status', 'teamTaskStatus_TL'])
                        ->whereHas('assignedTeams', function ($query) {
                            $query->whereIn('team_id', session('team_id'));
                        })
                        ->select('tasks.*')
                        ->orderBy('id', 'desc')
                        ->get();     
            return DataTables::of($projects)
                ->addColumn('task_status', function ($row) {
                    return optional($row->status)->proj_status_name;
                })
                 ->addColumn('task_created_by', function ($row) {
                    return optional($row->task_owner)->name;
                })
                ->addColumn('team_status', function ($row) use ($edit_permit){
                    $teamStatus = $row->teamTaskStatus_TL;
                    $status = 'Not Assigned';
                    if ($teamStatus && $teamStatus->status) {
                        if ($teamStatus->status->id == config('global.approval_waiting_status')) {
                            $status = $edit_permit ? '<button type="button" id="UpdateStat" class="btn btn-danger btn-sm" data-id="'.$row->id.'" data-bs-placement="top" title="Edit Approval"><span class="blinking">' . $teamStatus->status->proj_status_name . '</span></button>' : '<span class="blinking">' . $teamStatus->status->proj_status_name . '</span>';
                        } else {
                            $status = $teamStatus->status->proj_status_name;
                        }
                    }
                    $deadline = '';
                    $starIcon = '⭐';
                    if (
                        $teamStatus &&
                        $teamStatus->team_status != config('global.completed_status') &&
                        $row->endDate &&
                        Carbon::now()->greaterThan(Carbon::parse($row->endDate))
                    ) {
                        $deadline = ' <span class="blinking" title="Deadline">' . $starIcon . '</span>';
                    }
                    return $status . $deadline;
                })
                ->addIndexColumn()
                ->addColumn('action', function($row)  use ($view_permit,$edit_permit, $delete_permit)  {
                    $editButton = $edit_permit
                            ? '<a href="' . route("tasks.edit_assign_task", $row->id) . '" class="btn btn-primary btn-sm"  data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Task"><i class="fa fa-edit"></i></a>' 
                            : '';

                    $taskAssignButton = $view_permit
                                ? '&nbsp;|&nbsp;<button type="button" id="ViewStat" class="btn btn-warning btn-sm" data-id="'.$row->id.'" data-bs-placement="top" title="Edit Team Members"><i class="fa fa-tasks" aria-hidden="true"></i></button>' 
                                : '';

                    $changeStateButton = $edit_permit 
                                ? '&nbsp;|&nbsp;<button type="button" id="UpdateStat" class="btn btn-danger btn-sm" data-id="'.$row->id.'" data-bs-placement="top" title="Edit Approval"><i class="fa fa-check" aria-hidden="true"></i></button>' 
                                : '';

                    $deleteButton = $delete_permit
                                ? '&nbsp;|&nbsp;<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'" data-bs-placement="top" title="Delete Task"><i class="fa fa-trash-o"></i></button>' 
                                : '';
                    $changeIndivStatButton='';
            $mon_role=config('global.task_monitor_roles');
            if(in_array(session('role_id'),$mon_role)){
                    $changeIndivStatButton ='&nbsp;|&nbsp;<button type="button" id="ChangeStat" class="btn btn-success btn-sm" data-id="'.$row->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
            }

                return $editButton . $taskAssignButton.$changeStateButton . $changeIndivStatButton . $deleteButton;
                })
                ->rawColumns(['task_status', 'team_status', 'action'])
                ->make(true);
        }
       return view('tasks.manage_main_tasks',compact('project_status','task_status','LoadDatatables'));
    }
      /**For TL and PL */
    public function ViewMainTasksLevel($request,$current_status='') {
        $LoadDatatables=true;   
         $project_status= ProjectStatus::where('task_set_status',config('global.task_set_status'))->get();
         $task_status=ProjectStatus::where('emp_set_status',config('global.emp_set_status'))->get();
         $emp_id=session('user_id');
         $view_permit=PermissionHelper::checkPermission('global.categories',$this->view_perm);
        $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
        $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);
        $query = Tasks::with(['project','myAssignedInfo', 'status', 'teamTaskStatus_TL', 'empTaskStatus.status','assignedEmployees'])
                                    ->whereHas('assignedEmployees', function ($query_tst) {
                                        $query_tst->where('employee_id', session('user_id'))
                                                    ->whereIn('team_id',session('team_id'));
                                    });
                                     if(isset($current_status) && !empty($current_status)){
                                        $statusName = str_replace('_',' ',$current_status); 
                                        $statusId = config('global_task_status')[$statusName] ?? null;
                                        $query->whereHas('teamTaskStatus_TL', function ($query_ts) use ($statusId) {
                                            $query_ts->where('team_status', $statusId)
                                                    ->whereIn('team_id', session('team_id'));
                                        });
                                     }
                                $query->orderBy('id', 'desc');
                                
                        $projects=$query->get();
        if ($request->ajax()) {
                
            return DataTables::of($projects)
             ->addColumn('main_task_name', function ($row) {
                     return $row->task_name;
                })
             ->addColumn('task_name', function ($row) {
                     return optional($row->myAssignedInfo)->task_info ?? 'Not Assigned';
                })
             ->addColumn('project_name', function ($row) {
                     return optional($row->project)->proj_name ?? 'Not Assigned';
                })
                ->addColumn('task_status', function ($row) {
                     return optional($row->status)->proj_status_name ?? 'Not Assigned';
                })
               ->addColumn('emp_task_status', function ($row) {
                        return optional(optional($row->empTaskStatus)->status)->proj_status_name ?? 'Not Assigned';
                    })
          ->addColumn('team_status', function ($row) use ($edit_permit) {
              $status = 'Not Started'; // Default value

            if (!empty($row->teamTaskStatus_TL) && !empty($row->teamTaskStatus_TL->status)) {
                if ($row->teamTaskStatus_TL->status->id == config('global.approval_waiting_status')) {
                    $status = $edit_permit 
                                ?  '<button type="button" id="UpdateStat" class="btn btn-danger btn-sm" data-id="'.$row->id.'" data-bs-placement="top" title="Edit Approval"><span class="blinking">' . $row->teamTaskStatus_TL->status->proj_status_name . '</span></button>'
                                :  '<span class="blinking">' . $row->teamTaskStatus_TL->status->proj_status_name . '</span>';
                    
                } else {
                    $status = $row->teamTaskStatus_TL->status->proj_status_name;
                }
            }
                $deadline = '';
                $starIcon = '⭐';
                if (Carbon::now()->greaterThan(Carbon::parse($row->endDate ?? now())) && optional($row->teamTaskStatus_TL)->team_status == config('global.approval_waiting_status')) {
                    $deadline = ' <span class="blinking" title="Deadline">' . $starIcon . '</span>';
                }
                return $status . $deadline;
            })
              ->addColumn('task_created_by', function ($row) {
                    return optional($row->task_owner)->name;
                })
                ->addIndexColumn()
                ->addColumn('action', function($row)  use ($view_permit,$edit_permit, $delete_permit) {
                    $editButton = $edit_permit
                            ? '<a href="' . route("tasks.edit_assign_task", $row->id) . '" class="btn btn-primary btn-sm"  data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Task"><i class="fa fa-edit"></i></a>' 
                            : '';

                    $taskAssignButton = $view_permit
                                ? '&nbsp;|&nbsp;<button type="button" id="ViewStat" class="btn btn-warning btn-sm" data-id="'.$row->id.'" data-bs-placement="top" title="Edit Team Members"><i class="fa fa-tasks" aria-hidden="true"></i></button>' 
                                : '';

                    $changeStateButton = $edit_permit 
                                ? '&nbsp;|&nbsp;<button type="button" id="UpdateStat" class="btn btn-danger btn-sm" data-id="'.$row->id.'" data-bs-placement="top" title="Edit Approval"><i class="fa fa-check" aria-hidden="true"></i></button>' 
                                : '';

                    $deleteButton = $delete_permit 
                                ? '&nbsp;|&nbsp;<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'" data-bs-placement="top" title="Delete Task"><i class="fa fa-trash-o"></i></button>' 
                                : '';
                    $changeIndivStatButton='';
            $mon_role=config('global.task_monitor_roles');
            if(in_array(session('role_id'),$mon_role)){
                    $changeIndivStatButton ='&nbsp;|&nbsp;<button type="button" id="ChangeStat" class="btn btn-success btn-sm" data-id="'.$row->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
            }

    return $editButton . $taskAssignButton . $changeIndivStatButton . $deleteButton;
    //.$changeStateButton
                })
                ->rawColumns(['task_status', 'team_status', 'action'])
                ->make(true);
        }
        return view('tasks.manage_tasks',compact('project_status','task_status','LoadDatatables')); // Ensure view is returned when request isn't AJAX
    }
     /**For all Employees */
    public function ViewIndividualTasks($request,$current_status=''){
        $LoadDatatables=true;  
        $query_fetch = TaskAssignEmp::where('employee_id', session('user_id'))
            ->join('tasks', 'tasks.id', '=', 'task_assign_emp.task_id')
            ->whereNull('tasks.deleted_at') // Moved after join
            ->join('project_status as task_status_table', 'task_status_table.id', '=', 'tasks.task_status')
            ->join('project_status as emp_status_table', 'emp_status_table.id', '=', 'task_assign_emp.emp_task_status')
            ->join('task_status_team', 'task_status_team.task_id', '=', 'task_assign_emp.task_id')
            ->join('project_status as team_status_table', 'team_status_table.id', '=', 'task_status_team.team_status');
            $query_fetch->whereIn('task_status_team.team_id', session('team_id'));
            if(isset($current_status) && !empty($current_status)){
                $statusName = str_replace('_',' ',$current_status); // from route or request
                $statusId = config('global_task_status')[$statusName] ?? null;
                $query_fetch->where('task_assign_emp.emp_task_status',$statusId );
            }
            $query_fetch->with(['task', 'task.project', 'status']) // Ensure these relationships exist
            ->select(
                'task_assign_emp.*',
                'tasks.task_name',
                'tasks.endDate',
                'tasks.task_status',
                'task_status_team.team_status',
                'task_status_table.proj_status_name as task_status_name',
                'emp_status_table.proj_status_name as emp_task_status_name',
                'team_status_table.proj_status_name as team_task_status_name'
            )
            ->orderBy('task_assign_emp.id', 'desc');
           // This will print the full SQL with values
            // $sql = vsprintf(
            //     str_replace('?', "'%s'", $leaves->toSql()),
            //     $leaves->getBindings()
            // );
          // echo $sql;
        $tasks = $query_fetch->get();
        $project_status= ProjectStatus::where('emp_set_status',config('global.emp_set_status'))->get(); 
           // @dd($tasks);
        if ($request->ajax()) {     
                
            return DataTables::of($tasks)
                ->addColumn('main_task_name', function ($row) {
                            return $row->task->task_name ?? 'No Task Name';
                        })
                    ->addColumn('task_name', function ($row) {
                        return $row->task_info ?? 'No Task Name';
                    })
                     ->addColumn('endDate', function ($row) {
                        return $row->endDate ?? 'No proj_id';
                    })
                    ->addColumn('project_name', function ($row) {
                            return optional(optional($row->task)->project)->proj_name ?? 'No Project'; 
                        })
                    ->addColumn('task_status', function ($row) {
                        $alert='';
                         if ($row->team_status == config('global.approval_waiting_status')) {
                             $alert='-'.' <span class="blinking" title="Deadline">Waiting for Approval</span>';
                        }
                        return optional($row->status)->proj_status_name.$alert ?? 'Status Unknown';
                    })
                      ->addColumn('task_created_by', function ($row) {
                            return optional(optional($row->task)->task_owner)->name;
                        })
                    ->addIndexColumn()
                    ->addColumn('action', function ($row) {
                        $edit=PermissionHelper::checkPermission('global.categories',$this->view_perm) 
                                ? '&nbsp;|&nbsp;<button class="btn btn-primary btn-sm" data-id="'.$row->id.'" id="EditStat"  data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Task Status"><i class="fa fa-edit"></i></button>'  : '';
                        return ' <button type="button" id="ViewStat" class="btn btn-warning btn-sm" data-id="'.$row->task_id.'"  data-bs-toggle="tooltip" data-bs-placement="top" title="View Task Members"><i class="fa fa-list-alt"></i></button>'.$edit;
                    })
                    ->rawColumns(['action','task_status'])
                    ->make(true);
        }
            return view('tasks.manage_individual_tasks',compact('project_status','LoadDatatables'));
    }
    public function assign_tasks(){
        $project_type = ProjectType::all();
        $team_type = TeamType::all();
        if (!PermissionHelper::checkPermission('global.categories', $this->add_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $LoadMultiselectJS=true;
        $LoadDateTimepicker=true;
        return view('tasks.assign_tasks', compact('project_type', 'team_type','LoadMultiselectJS','LoadDateTimepicker'));
    }
    public function create_task(Request $request) {
            try{
                    $validated = $request->validate([
                        'task_name' =>[
                                        'required',
                                        Rule::unique('tasks', 'task_name')->whereNull('deleted_at')
                                    ],
                        'startDate'=> 'required',
                        'endDate'=> 'required',
                        'proj_typ_id'=> 'required',
                        'proj_id'=> 'required',
                        'team_type.*'=> 'required',
                        'team_ids.*'=> 'required',
                        'team_members_ids.*'=> 'required',
                    ]);
                    $all_emp_ids=[];
                        $data=$request->only(['task_name', 'proj_typ_id', 'proj_id']);
                        $data['startDate'] = Carbon::createFromFormat('d-m-Y H:i:s', $request->startDate)->format('Y-m-d H:i:s');
                        $data['endDate'] = Carbon::createFromFormat('d-m-Y H:i:s', $request->endDate)->format('Y-m-d H:i:s');
                        $data['team_typ_id']=implode(',',$request->team_type);
                        $data['created_by']=session('user_id');
                        $task = Tasks::create($data);
                        // Insert team_id and emp_id into task_assignments
                            foreach ($request->team_ids as $teamId) {
                                DB::table('task_assign_team')->insert([
                                    'team_id' => $teamId,
                                    'task_id' => $task->id,
                                ]);
                                DB::table('task_status_team')->insert([
                                    'team_id' => $teamId,
                                    'task_id' => $task->id,
                                    'team_status' => config('global.not_started')
                                ]);
                            }       
                        $fetch_manage_heads=TeamMembers::whereIn('team_id',$request->team_ids)
                                                        ->where('ctrl_status',config('global.ctrl_status'))
                                                        ->get()
                                                        ->select('emp_id','team_id');
                        //     $ctrl_emp_ids=$fetch_manage_heads->pluck('emp_id');
                        // array_push($all_emp_ids,$ctrl_emp_ids);
                            foreach($fetch_manage_heads as $ptl){
                                     DB::table('task_assign_emp')->insert([
                                            'employee_id' => $ptl['emp_id'],
                                            'task_id' => $task->id,
                                            'task_info'=>$request->task_name,
                                            'ctrl_status'=>config('global.ctrl_status'),
                                            'team_id' => $ptl['team_id']
                                        ]);
                                        $all_emp_ids[ $ptl['emp_id']] =config('global.ctrl_status');
                            } 
                            foreach ($request->team_members_ids as $memberId) {
                                $split_info=explode('--',$memberId);
                                $emp_id=$split_info[0];
                                $team_id=$split_info[1];
                                $tas_name =  $request->assign_mem_task[$emp_id] ?? '';
                                DB::table('task_assign_emp')->insert([
                                    'employee_id' => $emp_id,
                                    'team_id' => $team_id,
                                    'task_id' => $task->id,
                                    'task_info' =>$tas_name,
                                ]);
                                $all_emp_ids[$emp_id]=0;
                            }
                       $actorUserId = session('user_id');
                     $pmIds = DB::table('teams')
                            ->whereIn('teams.id', $request->team_ids)
                            ->join('team_type', 'team_type.id', '=', 'teams.team_type')
                            ->pluck('team_type.pm_id')
                            ->unique()
                            ->filter()
                            ->toArray();
                    foreach ($pmIds as $pmId) {
                        if (!isset($all_emp_ids[$pmId])) {
                            $all_emp_ids[$pmId] = 1; // or 1 if you want them as reporting
                        }
                    }
                    $notify_type="create_tasks";
                   $subject="Fortgrid - New Task created.";
                 $this->assignTask($task->id,$notify_type,$subject);                  
                        $log_name='tasks';
                        ActivityHelper::logActivity('New Task Created',$log_name, $task, [
                                    'request' => request()->all()
                                ]);
                        return redirect('manage_tasks')->withSuccess('Task Assigned successfully!');
                       // return response()->json(['message' => 'Task Assigned successfully!','task_id' =>$task->id],200);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
    }
public function edit_assign_task(Request $request,$id){
    if (!PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
        // Redirect if permission is denied
        return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
    } 
    $LoadMultiselectJS=true;
    $LoadDateTimepicker=true;
    $tasks = Tasks::with(['assignedTeams','assignedMembers'])->findOrFail($id);

    $datas['startDate'] = Carbon::createFromFormat('Y-m-d H:i:s',  $tasks['startDate'])->format('d-m-Y H:i:s');   
    $datas['endDate'] = Carbon::createFromFormat('Y-m-d H:i:s',  $tasks['endDate'])->format('d-m-Y H:i:s'); 
    $project_type = ProjectType::all();
    $projects = Projects::where('proj_type',$tasks['proj_typ_id'])->get();

    $team_type = TeamType::all();
    $datas['selected_team_type']=explode(',',$tasks['team_typ_id']);
    
    $datas['all_teams']=Teams::whereIn('team_type', $datas['selected_team_type'])->pluck('team_name','id')->toArray();
   
    $datas['selected_teams'] = $tasks->assignedTeams()->pluck('teams.id')->toArray();
    $teamIds=$datas['selected_teams'];
    $datas['other_team_members'] = TeamMembers::where('ctrl_status', '!=', config('global.ctrl_status'))
    ->whereNotIn('team_id', $teamIds)
    ->pluck('emp_id')
    ->toArray();
/**Team Members show differ for PM and management based roles */
   $query = TeamMembers::with('user','team', 'user.roles')
             ->where('ctrl_status','!=',config('global.ctrl_status'))
            ->whereHas('user', function ($query) {
                $query->where('emp_status', config('global.active_status'));
            })
             ->whereHas('user.roles', function ($query) {
                    $query->whereNotIn('roles_id', config('global.task_approve_roles')); // Adjust condition as needed
                })
                 ->join('teams', 'team_members.team_id', '=', 'teams.id');

    if(in_array(session('role_id'), config('global.task_monitor_roles'))){
            $alerted_teams=TeamMembers::with('teams')->where('emp_id',session('user_id'))
                           ->where('ctrl_status',config('global.ctrl_status'))->pluck('team_id')->toArray();
             $query->whereIn('team_id', $alerted_teams);
        }else {
             
            $query ->whereIn('team_id', $teamIds);
        }
        
  $datas['team_members'] = $query ->whereNotNull('team_id') // Ensure team_id exists
                                 ->whereHas('team')
                                  ->get()
                                    ->map(function ($member) {
                                            return [
                                            'id' => $member->user->id ?? null,
                                            'name' => $member->user->name ?? 'Unknown',
                                            'team_name' => $member->team->team_name ?? 'No Team',
                                            'team_id' => $member->team->id ?? null,
                                        ];

                                      });                            

    $assignedMembersWithTeam = $tasks->assignedMembers->map(function ($member) use ($datas) {
    //$teamId = collect($datas['team_members'])->firstWhere('id', $member->id)['team_id'] ?? 'No Team Assigned';
    $teamName = Teams::where('id', $member->pivot->team_id)->pluck('team_name')->first();

    return [
        'employee_id' => $member->id,
        'name' => $member->name,
        'team_id' => $member->pivot->team_id,
        'team_name' =>$teamName,
        'pivot_id' =>  $member->pivot->id,
        'task_info'=>  $member->pivot->task_info,
        'comments'=>  $member->pivot->comments,
    ];
});
$datas['assignedMembersWithTeam']=$assignedMembersWithTeam->sortByDesc('team_id')->values();
//@dd($assignedMembersWithTeam);
$taskId = $id;
$empId = session('user_id');
$ctrlStatus = config('global.ctrl_status');

// Fetch data using Eloquent relationships

    $datas['cntrl_teams'] = TaskAssignTeam::where('task_id', $taskId)
    ->whereHas('teamMembers', function ($query) use ($ctrlStatus, $empId) {
        $query->where('ctrl_status', $ctrlStatus)
              ->where('emp_id',$empId);
    })
    ->pluck('team_id')->toArray();
    
    $datas['selected_employees'] = $tasks->assignedMembers->pluck('pivot.employee_id')->toArray(); 
    
    return view('tasks.edit_assign_tasks', compact('tasks','project_type','projects', 'team_type','datas','LoadMultiselectJS','LoadDateTimepicker'));
}
public function update_task(Request $request,$id) {
    
            $validated = $request->validate([
                'task_name' => [
                        'required',
                        Rule::unique('tasks', 'task_name')
                            ->whereNull('deleted_at') // Exclude soft deleted records
                            ->ignore($id) // Ignore the current record
                    ],
                'startDate'=> 'required',
                'endDate'=> 'required',
                'proj_typ_id'=> 'required',
                'proj_id'=> 'required',
                'team_type.*'=> 'required',
                'team_ids.*'=> 'required',
                'team_members_ids.*'=> 'required',
            ]);
                $data=$request->only(['task_name', 'proj_typ_id', 'proj_id']);
                $data['startDate'] = Carbon::createFromFormat('d-m-Y H:i:s', $request->startDate)->format('Y-m-d H:i:s');
                $data['endDate'] = Carbon::createFromFormat('d-m-Y H:i:s', $request->endDate)->format('Y-m-d H:i:s');
                $data['team_typ_id']=implode(',',$request->team_type);
                
                $task = Tasks::where('id', $id)->update($data);
                // Insert team_id and emp_id into task_assignments
                DB::table('task_assign_team')->where('task_id', $id)->delete();
                // DB::table('task_assign_emp')->where('task_id', $id)->delete();
               
              
                $team_ids = $request->team_ids; // Directly use dropdown selection as array
             
                foreach ($team_ids as $teamId) {
                   // @dd($teamId);
                    DB::table('task_assign_team')->insert([
                        'team_id' => $teamId,
                        'task_id' => $id
                    ]);
                }
        
                $team_mem_ids = $request->assign_mem_id; // Directly use dropdown selection as array
      
                foreach ($team_mem_ids as $memberId) {
                     $tas_name =  $request->assign_mem_task[$memberId];
                     //echo  $tas_name;
                           DB::table('task_assign_emp')
                            ->where('task_id',$id)
                            ->where('employee_id',$memberId)
                            ->update([
                                'task_info' =>$tas_name,
                                'team_id' =>$request->assign_team_id[$memberId],
                          ]);
                    }
                    $notify_type="update_tasks";
                    $subject="Fortgrid - Updating Task Details.";
                   $this->assignTask($id,$notify_type,$subject);
                    $log_name='tasks';
                    ActivityHelper::logActivity('Task Updated',$log_name, $task, [
                                'request' => request()->all()
                            ]);
                return redirect('manage_tasks')->withSuccess('Task Updated successfully!');
  }

  public function destroy_task($id){
                $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
            if(!$cat_permission){ 
                return  response()->json(['message' => 'Not Authorized to see this page.'],200);
            }else{
                $user = Auth::user();
                $tasks = Tasks::find($id);
            if ($tasks) {
            $log_name='tasks';
                ActivityHelper::logActivity('Task Deleted',$log_name, $tasks, [
                            'request' => request()->all()
                        ]);
                    $tasks->delete();
            }
                return response()->json(['message' => 'Record Deleted successfully!'],200);
                }
  }
public function deleted_tasks(Request $request){

        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }

        if ($request->ajax()) {
                $tasks = Tasks::onlyTrashed()->get();
                $log_name='tasks';
                ActivityHelper::logActivity('View Deleted Tasks',$log_name, '', [
                    'request' => request()->all()
                ]);

                return DataTables::of($tasks)
                    ->addIndexColumn()
                        ->addColumn('action', function($row) {
                        
                        return '<button type="button"  class="btn btn-dark btn-sm restore-btn" data-id="'.$row->id.'">
                                <i class="fa fa-refresh" aria-hidden="true"></i></button>';
                        })
                    ->rawColumns(['action'])
                    ->make(true);
         }
        return view('tasks.deleted_tasks',['LoadDatatables' => true]);
}
  public function restore_deleted($id){
        $tasks=Tasks::withTrashed()->find($id);
        $log_name='tasks';
        ActivityHelper::logActivity('Restore Deleted Tasks',$log_name, $tasks, [
            'request' => request()->all()
        ]);
        $tasks->restore();
        return response()->json(['message' => 'Tasks Restored successfully!'],200);
        }
public function getAssignedMembers(Request $request)
{
   $teamIds=$request->teams;    
    $query = TeamMembers::with('user','team', 'user.roles')
            ->where('ctrl_status','!=',config('global.ctrl_status'))
             ->where('emp_id','!=', session('user_id'))
             ->whereIn('emp_id',$request->members)
            ->whereHas('user', function ($query) {
                $query->where('emp_status', config('global.active_status'));
            })
             ->whereHas('user.roles', function ($query) {
                    $query->whereNotIn('roles_id', config('global.task_approve_roles')); // Adjust condition as needed
              });
            

            $query ->whereIn('team_id', $teamIds);
        $query->whereNotNull('team_id') // Ensure team_id exists
                      ->whereHas('team')
                      ->get();


$team_members = $query->whereNotNull('team_id') // Ensure team_id exists
                      ->whereHas('team')
                      ->get()
                      ->map(function ($member) {
                             $teamName = Teams::where('id', $member->team->id)->pluck('team_name')->first(); // Fetch team_name using team_id
                             if(in_array($member->team->id,session('team_id'))){
                                $team_owner='own';
                             } else {
                                $team_owner='other';
                             }
                          return [
                                'id' => $member->user->id ?? null,
                                'name' => $member->user->name ?? 'Unknown',
                                'team_name' => $teamName,
                                'team_id' => $member->team->id ?? null,
                                'task_info' => '' ,
                                'team_owner' =>$team_owner,
                            ];
                        });
    return response()->json($team_members);
}
public function getAssignedMembersInfo($taskId)
{  
     $tasks = Tasks::with(['assignedTeams','assignedMembers'])->findOrFail($taskId);
    $teamIds = $tasks->assignedTeams()->pluck('teams.id')->toArray();
    $alerted_teams = session('team_id');

    $query = TeamMembers::with('user', 'team', 'user.roles')
        ->where('ctrl_status', '!=', config('global.ctrl_status'))
        ->where('emp_id', '!=', session('user_id'))
        ->whereHas('user', function ($query) {
            $query->where('emp_status', config('global.active_status'));
        })
        ->whereHas('user.roles', function ($query) {
            $query->whereNotIn('roles_id', config('global.task_approve_roles'));
        })
        ->orderBy('team_id', 'DESC'); // Ensure sorting

    if (in_array(session('role_id'), config('global.task_monitor_roles'))) {
        $alerted_teams = TeamMembers::with('teams')->where('emp_id', session('user_id'))
            ->where('ctrl_status', config('global.ctrl_status'))->pluck('team_id')->toArray();
    } else {
        $query->whereIn('team_id', $teamIds);
    }

    $team_members = $query->whereNotNull('team_id') 
        ->whereHas('team')
        ->get()
        ->map(function ($member) {
            $teamName = Teams::where('id', $member->team->id)->pluck('team_name')->first();
            return [
                'id' => $member->user->id ?? null,
                'name' => $member->user->name ?? 'Unknown',
                'team_name' => $teamName,
                'team_id' => $member->team->id ?? null,
            ];
        })
        ->sortByDesc('team_id') // Ensure sorting at collection level
        ->values();

    $assignedMembersWithTeam = $tasks->assignedMembers->map(function ($member) use ($team_members) {
        $teamInfo = collect($team_members)->firstWhere('team_id', $member->pivot->team_id);
        $team_owner = in_array($member->pivot->team_id, session('team_id')) ? 'own' : 'other';

        return [
            'employee_id' => $member->id,
            'emp_name' => $member->name,
            'team_id' => $member->pivot->team_id,
            'team_name' => $teamInfo['team_name'] ?? 'No Team Assigned',
            'pivot_id' =>  $member->pivot->id,
            'task_info' =>  $member->pivot->task_info,
            'comments' =>  $member->pivot->comments,
            'task_status_id' =>$member->pivot->emp_task_status,
            'task_status' => ProjectStatus::find($member->pivot->emp_task_status)?->proj_status_name ?? 'Unknown Status',
            'team_owner' => $team_owner,
        ];
    })
    ->sortByDesc('team_id') // Sort members by team_id
    ->values(); // Reset array keys
 
   $report_members = TaskAssignEmp::with('user')
                    ->where('task_id', $taskId)
                    ->where('ctrl_status', '=', config('global.ctrl_status'))
                    ->get()
                    ->pluck('user.name')
                    ->toArray();
    /**Teams Alerted for PL or TL 
     * For showing unalerted members in team for TL or PL
    */
 $assignedIds = TaskAssignEmp::where('task_id', $taskId)
                         ->whereIn('team_id',  $teamIds)
                        ->where('ctrl_status', '!=', config('global.ctrl_status'))
                                ->pluck('employee_id')->toArray(); 
$alerted_teams=session('team_id');
$common_team = array_intersect($alerted_teams, $teamIds);


// $unassigned_mem_query = TeamMembers::whereNotIn('emp_id',  $assignedIds)
//  ->whereIn('team_id',  $teamIds)
//     ->where('ctrl_status', '!=', config('global.ctrl_status'))
//     ->with(['user.roles', 'team'])
//     ->whereHas('user', function ($query) {
//          $query->where('emp_status', config('global.active_status'));
//     })
//     ->whereHas('user.roles', function ($query) {
//         $query->whereNotIn('roles_id', config('global.task_approve_roles'));
//     });

$unassigned_mem_query = TeamMembers::where('ctrl_status', '!=', config('global.ctrl_status'))
    ->with(['user.roles', 'team'])
    ->whereNotIn('emp_id', function ($subquery) use ($taskId, $teamIds) {
        $subquery->select('employee_id')
            ->from('task_assign_emp')
            ->where('task_id', $taskId)
            ->whereIn('team_id', $teamIds)
            ->whereNull('deleted_at');
    })
     ->whereHas('user', function ($query) {
         $query->where('emp_status', config('global.active_status'));
    })
    ->whereHas('user.roles', function ($query) {
        $query->whereNotIn('roles_id', config('global.task_approve_roles'));
    });
  
if(in_array(session('role_id'), config('global.task_monitor_roles'))){
    $unassigned_mem_query->whereIn('team_id',$common_team);
     $team_status=TaskStatusTeam::where('task_id',$taskId)->whereIn('team_id',$common_team)->pluck('team_status')->first();
} else {
    $unassigned_mem_query->whereIn('team_id', $common_team);
     $team_status=TaskStatusTeam::where('task_id',$taskId)->whereIn('team_id',$teamIds)->pluck('team_status')->first();
}        
   $unassignedEmployees=$unassigned_mem_query
                                    ->get()
                                    ->map(function ($member) {
                                         if(in_array($member->team->id,session('team_id'))){
                                            $team_owner='own';
                                        } else {
                                            $team_owner='other';
                                        }
                                                    return [
                                                        'id' => $member->user->id,
                                                        'name' => $member->user->name,
                                                        'team_name' => $member->team->team_name,
                                                        'team_id' => $member->team->id,
                                                        'team_owner' =>$team_owner,
                                                    ];
                                                }); 
    return response()->json(['members'=>$assignedMembersWithTeam,'unassign_Emp'=>$unassignedEmployees,'assignedIds' =>$assignedIds,'report_members' =>$report_members,'cntrl_teams'=> $alerted_teams,'team_status'=>$team_status]);
}
public function LoadUnassignedEmployees($taskId){
 $assignedIds = TaskAssignEmp::where('task_id', $taskId)
                            ->where('ctrl_status', '!=', config('global.ctrl_status'))
                            ->pluck('employee_id')->toArray(); 
 if(in_array(session('role_id'), config('global.task_monitor_roles'))){
     $empId = session('user_id');
    $ctrlStatus = config('global.ctrl_status');
                $teamIds = TaskAssignTeam::where('task_id', $taskId)
                ->whereHas('teamMembers', function ($query) use ($ctrlStatus, $empId) {
                    $query->where('ctrl_status', $ctrlStatus)
                        ->where('emp_id',$empId);
                })
                ->pluck('team_id')->toArray();  
            } else {
        $teamIds = TaskAssignTeam::where('task_id', $taskId)->pluck('team_id')->toArray();
            }
              $teamIdscol = array_intersect((array) session('team_id'), $teamIds);
$unassignedEmployees = TeamMembers::with('user', 'team', 'user.roles')
                        ->whereIn('team_id', $teamIdscol) // Filter team members by matching team_ids
                        ->whereNotIn('emp_id', $assignedIds) 
                        ->where('ctrl_status', '!=', config('global.ctrl_status'))
                        
                        ->whereHas('user', function ($query) {
                            $query->where('emp_status', config('global.active_status'));
                        })
                        ->whereHas('user.roles', function ($query) {
                            $query->whereNotIn('roles_id', config('global.task_approve_roles')); // Adjust condition as needed
                        })
                        ->distinct()
                        ->get()
                    ->map(function ($member) {
                         if(in_array($member->team->id,session('team_id'))){
                                $team_owner='own';
                             } else {
                                $team_owner='other';
                             }
                                    return [
                                        'id' => $member->user->id,
                                        'name' => $member->user->name,
                                        'team_name' => $member->team->team_name,
                                        'team_id' => $member->team->id,
                                        'team_owner' => $team_owner,
                                    ];
                                });   
    return response()->json(['unassign_Emp'=>$unassignedEmployees]);
}
public function update_task_info(Request $request){
try{

  foreach($request->assign_mem_id  as $index => $member){
  DB::table('task_assign_emp')->where('task_id', $request->recordTaskId)
  ->where('employee_id',$member )
  ->update([
          'task_info' => $request->assign_mem_task[$index],
           'updated_at' => now() //
 ]);
    }

      $log_name='tasks';
       ActivityHelper::logActivity('Task Assign Individually',$log_name, '', [
                  'request' => request()->all()
              ]);
     return redirect()->route('tasks.manage_tasks')->withSuccess('Task Assigned successfully!');
  } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
  }

}
public function get_task_info($id){
    $result=TaskStatusTeam::where('task_id',$id)->whereIn('team_id',session('team_id'))->select('team_status','comments')->first();
    $reopen_tsk=ReopenedTask::where('task_id',$id)
                           ->where('ctrl_status','!=',config('global.ctrl_status'))
                            ->whereIn('team_id',session('team_id'))
                            ->select('task_id', 'ctrl_status','reopen_type', DB::raw("GROUP_CONCAT(emp_id SEPARATOR ', ') as emp_ids"))
                            ->groupBy('task_id', 'ctrl_status','reopen_type')
                            ->first(); 
     return response()->json(['task' => $result,'reopen_tsk' =>$reopen_tsk],200);
}

public function user_status($id){
 $task_dtls= TaskAssignEmp::find($id); 

 return response()->json($task_dtls) ;  
}
public function pl_status($id){
    $task_dtls= TaskAssignEmp::where('task_id',$id)
                        ->where('employee_id',session('user_id'))->first(); 
    return response()->json($task_dtls) ;
 
}
public function task_ind_update(Request $request){
   /**Update Status as Request */
  $task_dtls= DB::table('task_assign_emp')
                ->where('task_id',$request->task_id)
                ->where('employee_id',session('user_id'))
                ->update([
                            'emp_task_status'=>$request->emp_proj_status,
                            'comments' => $request->comments,
                            'task_info'=> $request->task_name,
                        ]);
$log_name='tasks';
       ActivityHelper::logActivity('Task Status Updated Individually',$log_name, $task_dtls, [
                  'request' => request()->all()
              ]);
        /**If a Team member change their status as inprogress then team status is set as in progress. */
              $Recordexists = TaskAssignEmp::where('task_id', $request->task_id)
                                        ->whereIn('team_id',session('team_id') )
                                        ->where('ctrl_status','!=',config('global.ctrl_status'))
                                        ->where('emp_task_status',  config('global.in_progress'))
                                        ->whereNull ('deleted_at')
                                        ->exists();
                if($Recordexists){
                   TaskStatusTeam::where('task_id', $request->task_id)
                                 ->whereIn('team_id',session('team_id'))
                                 ->update([
                                    'team_status'=>config('global.in_progress')
                                 ]);
                        $task_dtls = TaskAssignEmp::where('task_id', $request->task_id)
                            ->where('employee_id', session('user_id'))
                            ->with('task') // assuming relationship is defined
                            ->first();
                        $proj_id = $task_dtls->task->proj_id ?? null;
                        Projects::where ('id',$proj_id)
                                    ->update([
                                        'proj_status'=>config('global.in_progress')
                                    ]);
                        
                }
        /**If a Team member change their status as inprogress then team status is set as in progress. */
    /**If TASK STARTED BY ALL TEAMS (IF SET AS IN PROGRESS) OVERALL TASK STATUS IS SET AS IN PROGRESS.*/
    $task_started = TaskAssignEmp::where('task_id', $request->task_id)
        ->whereIn('team_id', session('team_id'))
        ->where('emp_task_status', config('global.in_progress'))
        ->where('ctrl_status', '!=', config('global.ctrl_status'))
        ->whereNull('deleted_at')
        ->count() > 0; // or use ->exists()

    if ($task_started) {
        Tasks::where('id', $request->task_id)
            ->update([
                'task_status' => config('global.in_progress')
            ]);
    }
/**If TASK STARTED BY ALL TEAMS IF SET AS IN PROGRESS OVERALL TASK STATUS IS SET AS INPROGRESS.*/

/**If TASK STARTED BY ALL TEAMS IF SET AS COMPLETED OVERALL TASK STATUS IS SET AS COMPLETED.*/
$allCompleted = TaskAssignEmp::where('task_id', $request->task_id)
                                ->whereIn('team_id',session('team_id'))
                                ->where('emp_task_status', '!=', config('global.completed_status'))
                                ->where('ctrl_status','!=',config('global.ctrl_status'))
                                ->whereNull ('deleted_at')
                                ->count() == 0;
                               
    $present_status=TaskStatusTeam::where('task_id',$request->task_id)
                                    ->whereIn('team_id',session('team_id'))
                                    ->pluck('team_status')->first();

        if ($allCompleted && ($present_status!=config('global.completed_status') && $present_status!=config('global.reopen_status') )) {

              $approval_req= config('global.approval_waiting_status');
              
              $waitingTeamIds = TaskStatusTeam::where('task_id', $request->task_id)
                                    ->whereIn('team_id', session('team_id'))
                                    ->get()->pluck('team_id')->toArray();
             $current_task=  TaskStatusTeam::whereIn('team_id', $waitingTeamIds)
                                        ->where('task_id', $request->task_id)
                                        ->update([
                                            'team_status' => $approval_req,
                                            'updated_at' => now()
                                        ]);
                                       
                $TaskName=Tasks::where('id',$request->task_id)->pluck('task_name')->first(); 
             $TeamName=Teams::whereIn('id',$waitingTeamIds)->pluck('team_name')->first();
             
                                    $notify_type='team_status_update';
                                    $subject='Fortgrid- Task Waiting For Approval';
                                    $base_content['message']='<p>Following Team is Waiting for Approval to complete the task.The Details are as follows:</p>
                                    <p> Task Name:'.$TaskName.'</p>
                                    <p>Team Name: '.$TeamName.'</p>'
                                    ."<br/><br/><a href='" . route('tasks.manage_tasks') . "' target='_blank'>Click the Link For more details.</a><br/>";

                            $this->assignTaskStatus($request->task_id,$notify_type,$subject,$base_content);
        $log_name='tasks';
       ActivityHelper::logActivity('Task Update as Completed by team',$log_name, $current_task, [
                  'request' => request()->all()
              ]);
        }
        /**If TASK STARTED BY ALL TEAMS IF SET AS COMPLETED OVERALL TASK STATUS IS SET AS COMPLETED.*/
        
    return response()->json(['message' => 'Tasks Updated successfully!.'],200);
}
public function task_update_main(Request $request){
try{
    if(isset($request->assign_mem_id) && !empty($request->assign_mem_id)){
        foreach($request->assign_mem_id  as $index => $old_member){
           // echo $index .'=>'. $old_member.'<br/>';
              $task_emp=TaskAssignEmp::where('task_id',$request->recordTaskId)
                                ->where('employee_id',$old_member)->first();
                if(!empty( $task_emp)){
                     $task_emp->update([
                            'task_info' => $request->assign_mem_task[$old_member],
                            'updated_at' => now(),
                            'team_id'=>$request->assign_team_id[$old_member],
                            ]);
                }
        }
    }
    if(isset($request->new_members) && !empty($request->new_members)){
        foreach($request->new_members  as $index => $newmember){
              $split_info=explode('--',$newmember);
                $emp_id=$split_info[0];
                if(!empty($request->assign_team_id[$emp_id])){
                    $team_id=$request->assign_team_id[$emp_id];
                } else {
                    $team_id=$split_info[1];
                }
           // @dd($newmember);
            $chk=TaskAssignEmp::where('task_id',$request->recordTaskId)
                                ->where('employee_id',$emp_id)->first();
            if(empty( $chk)){
             $task_emp=DB::table('task_assign_emp')
                ->insert([
                 'task_info' => isset($request->assign_mem_task[$emp_id]) ? $request->assign_mem_task[$emp_id] : null,
                'updated_at' => now(),
                'employee_id'=> $emp_id ,
                'task_id'=>$request->recordTaskId,
                'team_id'=>$team_id,
                ]); 
            }
        }
    }
    // After inserting all new members:
        $task = Tasks::find($request->recordTaskId);
        $notify_type='new_task_member';
        $subject='Fortgrid - New Member Added to Task';
        $content=    'A new member has been added to the task: ' . $task->task_name;
        $this->assignTask($task->id,$notify_type,$subject,$content);
      $log_name='tasks';
       ActivityHelper::logActivity('Task Assign Individually',$log_name, '', [
                  'request' => request()->all()
              ]);
     return response()->json(['success' => 'Tasks Updated successfully!'],200);
     //return redirect()->route('tasks.manage_tasks')->withSuccess('Task Assigned successfully!');
  } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
  }
}
public function task_update_main_status(Request $request){
   // @dd($request);
    try{
    $task_dtls=DB::table('task_status_team')->where('task_id',$request->recordTaskMainId)
    ->whereIn('team_id',session('team_id'))
    ->update([
            'team_status'=>$request->task_status,
            'comments'=>$request->comments
        ]);
      $log_name='tasks';
      ActivityHelper::logActivity('Task Status Updated - Main Task.',$log_name, '', [
                  'request' => request()->all()
              ]);
    if(isset($request->reopen_type) && $request->task_status==config('global.reopen_status')){
        ReopenedTask::where('task_id', $request->recordTaskMainId)
                            ->whereIn('team_id',session('team_id'))
                            ->delete();
        $all_emp_ids=[];
        $team_ids_col=[];
        $uniqueTeamIds=[];
        if($request->reopen_type==2){
            foreach($request->status_emp as $emp_in){
                $split_info=explode('--',$emp_in);
                $emp_id=$split_info[0];
                $team_id=$split_info[1];
                $data = [
                    'task_id' => $request->recordTaskMainId,
                    'emp_id' => $emp_id,
                    'team_id' => $team_id,
                    'reopen_type' => $request->reopen_type,
                    'task_status' => config('global.reopen_pending'),
                ];
                array_push($team_ids_col,$team_id);
                $all_emp_ids[$emp_id] =0;
                ReopenedTask::insert($data);
               $indiv_task_update = TaskAssignEmp::where('task_id', $request->recordTaskMainId)
                                                    ->where('employee_id', $emp_id)
                                                    ->where('team_id', $team_id) 
                                                    ->update([
                                                        'emp_task_status' => config('global.reopen_status'),
                                                    ]);
            }
            $uniqueTeamIds = array_unique($team_ids_col);
        } else {
            $fetch_empl = $this->get_assigned_teams_members($request->recordTaskMainId)->getData();
            foreach($fetch_empl as $f_emp){
                 $data = [
                    'task_id' => $request->recordTaskMainId,
                    'emp_id' => $f_emp->employee_id,
                    'team_id' => $f_emp->team_id,
                    'reopen_type' => $request->reopen_type,
                    'task_status' => config('global.reopen_pending'),
                ];
                $all_emp_ids[$f_emp->employee_id] =0;
                array_push($team_ids_col,$f_emp->team_id);
                ReopenedTask::insert($data);
                 $indiv_task_update = TaskAssignEmp::where('task_id', $request->recordTaskMainId)
                                                    ->where('employee_id', $f_emp->employee_id)
                                                    ->where('team_id', $f_emp->team_id) 
                                                    ->update([
                                                        'emp_task_status' => config('global.reopen_status'),
                                                    ]);
            }
            $uniqueTeamIds = array_unique($team_ids_col);
        }
        $ctrl_teams_ids= TeamMembers::where('ctrl_status', '=', config('global.ctrl_status'))
                                    ->whereIn('team_id',$uniqueTeamIds)
                                    ->select('emp_id','team_id')->get();                
            foreach($ctrl_teams_ids as $ctrl_mem){
                            $data = [
                                'task_id' => $request->recordTaskMainId,
                                'emp_id' => $ctrl_mem->emp_id,
                                'team_id' => $ctrl_mem->team_id,
                                'reopen_type' => $request->reopen_type,
                                'task_status' => config('global.reopen_pending'),
                                'ctrl_status' => config('global.ctrl_status')
                            ];
                        ReopenedTask::insert($data);
                        $all_emp_ids[$ctrl_mem->emp_id] =config('global.ctrl_status');
                        /**When task reopened team flag set as reopened */
                         $indiv_task_update = TaskStatusTeam::where('task_id', $request->recordTaskMainId)
                                                    ->whereIn('team_id', $uniqueTeamIds) 
                                                    ->update([
                                                        'team_status' => config('global.reopen_status'),
                                                    ]);
            }
                $indiv_task_update = TaskAssignEmp::where('task_id', $request->recordTaskMainId)
                                                                    ->where('employee_id', $ctrl_mem->emp_id)
                                                                    ->where('team_id', $ctrl_mem->team_id) 
                                                                    ->update([
                                                                        'emp_task_status' => config('global.reopen_status'),
                                                                    ]);
                $task = Tasks::where('id',$request->recordTaskMainId)->pluck('task_name')->first();
                $notify_type='reopen_tasks';
                $subject="Fortgrid - Task reopened.";
                $content['message']= '<p>Following Task was reopened. The Task Details are as follows:</p>
                            <p>Task Name: ' . $task.'</p>
                            <p>Comments:'.$request->comments.'</p>';
                $this->assignTask($request->recordTaskMainId,$notify_type,$subject,$content, $all_emp_ids);                                                                     
    } 
      if(isset($request->task_status) && $request->task_status==config('global.completed_status')){
        $notify_type="team_status_update";
        $subject="Fortgrid - Overall Team Update";
         $waitingTeamIds = TaskStatusTeam::where('task_id', $request->recordTaskMainId)
                                    ->whereIn('team_id',session('team_id'))
                                    ->get()->pluck('team_id')->toArray();
                                    
        $TaskName=Tasks::where('id',$request->recordTaskMainId)->pluck('task_name')->first(); 
        $TeamName=Teams::whereIn('id',$waitingTeamIds)->pluck('team_name')->first();
        $comments=$request->comments?$request->comments:'No Comments';
        $base_content['message']='<p>Following Team Completed the task.The Details are as follows:</p>
                                    <p> Task Name:'.$TaskName.'</p>
                                    <p>Team Name: '.$TeamName.'</p>
                                    <p>Comments: '.$comments.'</p>'
                                    ."<br/><br/><a href='" . route('tasks.manage_tasks') . "' target='_blank'>Click the Link For more details.</a><br/>";
        $this->assignTaskStatus($request->recordTaskMainId,$notify_type,$subject,$base_content);                                    
   }
   $statuses = TaskStatusTeam::where('task_id', $request->recordTaskMainId)
                            ->pluck('team_status')
                            ->unique();
// Check if there is only one unique team_status
if ($statuses->count() === 1) {
    $complete_req=config('global.completed_status');
    $current_task= DB::table('tasks')->where('id', $request->recordTaskMainId)
            ->update([
                    'task_status' => $complete_req,
                    'updated_at' => now() 
            ]);
               $notify_type="task_status_update";
        $subject="Fortgrid - Overall Update";
        $TaskName=Tasks::select('proj_id', 'task_name')
                        ->where('id', $request->recordTaskMainId)
                        ->first()->toArray();
        $base_content['message']='<p>Following Task was Completed by all teams.The Details are as follows:</p>
                                    <p> Task Name:'.$TaskName['task_name'].'</p>'
                                    ."<br/><br/><a href='" . route('tasks.manage_tasks') . "' target='_blank'>Click the Link For more details.</a><br/>";
        $this->assignTaskStatus($request->recordTaskMainId,$notify_type,$subject,$base_content); 
        $log_name='tasks';
       ActivityHelper::logActivity('Task Update as Completed by all team',$log_name, $current_task, [
                  'request' => request()->all()
              ]);
        $counts = Tasks::where('proj_id', $TaskName['proj_id'])
            ->selectRaw('COUNT(*) as total_tasks, SUM(task_status = '.$complete_req.') as completed_tasks')
            ->first();
            if ($counts->total_tasks > 0 && $counts->total_tasks == $counts->completed_tasks) {
                $current_project=Projects::where('id', $TaskName['proj_id'])->update(['proj_status' => $complete_req]);
                $log_name='projects';
       ActivityHelper::logActivity('Project Status Update as Completed.',$log_name, $current_project, [
                  'request' => request()->all()
              ]);
            }
 }
    //  return redirect()->route('tasks.manage_tasks')->withSuccess('Task Status Updated successfully!');
     return response()->json(['success' => 'Task Status Updated successfully!']);
  } catch (\Exception $e) {
      return response()->json(['message' => $e->getMessage()], 500);
  }
}

public function view_user_status($id){
    $task_dtls=  TaskAssignEmp::where('task_id',$id) // Replace with session('user_id') dynamically
                ->whereNull('tasks.deleted_at') // Ensure soft delete records are excluded
                ->join('tasks', 'tasks.id', '=', 'task_assign_emp.task_id') // Join tasks table
                ->with(['task','task.project', 'status']) // Load related models
                ->select('task_assign_emp.*', 'tasks.task_name') // Fetch required columns
                ->get();
    $project_status= ProjectStatus::get();
    return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($task_dtls) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
}
public function remove_task_employee(Request $request)
{
        $taskAssign = TaskAssignEmp::findOrFail($request->itemId);
        $removedEmpId = $taskAssign->employee_id;
        $taskId = $taskAssign->task_id;

        $removedUser = User::find($removedEmpId);
        $task = Tasks::find($taskId);
        if ($taskAssign) {
                $log_name='tasks';
                    ActivityHelper::logActivity('Task Assigned Employee Deleted',$log_name, $taskAssign, [
                                'request' => request()->all()
                            ]);
                }
        // Delete the record
        $taskAssign->delete();
        
        $notify_type='remove_task_notice';
        $subject='Fortgrid - Task member removed';
        $base_content['subject']= 'Fortgrid - You are removed from the task.';
        $base_content['message']= "<p>The user {$removedUser->name} has been removed from the task: {$task->task_name}.</p>";
        $this->assignTask($taskId,$notify_type,$subject,$base_content,['id' => $removedUser->id, 'name' => $removedUser->name]
    );
    return response()->json(['message' => 'Member removed and notifications sent.']);
}
public function task_employee_update(Request $request) {
    // Ensure member_ids is an array of integers
    $selected_mem = is_array($request->member_ids) 
        ? array_map('intval', $request->member_ids) 
        : (empty($request->member_ids) ? [] : array_map('intval', explode(',', $request->member_ids)));
    
    if(in_array(session('role_id'),config('global.task_monitor_roles'))){
        $ctrl_teams_ids= TeamMembers::where('ctrl_status', '=', config('global.ctrl_status'))
                                    ->where('emp_id',session('user_id'))
                                    ->pluck('team_id')->toArray();

        $other_team_mem = DB::table('team_members as ts')
            ->join('task_assign_team as tas', function ($join) use($request, $ctrl_teams_ids) {
                $join->on('ts.team_id', '=', 'tas.team_id')
                    ->where('tas.task_id', $request->taskId)
                    ->whereNotIn('tas.team_id', $ctrl_teams_ids); // Fixed variable name
            })
            ->pluck('emp_id')
            ->toArray();
    
                        // Fetch existing assigned employees
$existing_team_mem = TaskAssignEmp::where('task_id', $request->taskId)
                            ->where('ctrl_status','!=',1)
                            ->whereNotIn('employee_id',$other_team_mem)
                            ->pluck('employee_id')
                            ->map(fn($id) => intval($id))
                            ->toArray();
   
    } else{
            // Fetch existing assigned employees
    $existing_team_mem = TaskAssignEmp::where('task_id', $request->taskId)
                                        ->where('ctrl_status','!=',1)
                                      ->pluck('employee_id')
                                      ->map(fn($id) => intval($id))
                                      ->toArray();
    }

    // Find new members and members to remove
    $new_memberss = array_diff($selected_mem, $existing_team_mem);
    $old_members_remove = array_diff($existing_team_mem, $selected_mem);  
    $updated_members = array_intersect($selected_mem, $existing_team_mem);

if (!empty($new_memberss)) {
    // Ensure team IDs are properly mapped
    $teamIds = array_values($request->team_ids); // Get indexed array

    $insertData = array_map(fn($index) => [
        'employee_id' => $new_memberss[$index],
        'task_id' => $request->taskId,
        'created_at' => now(),
        'team_id' => $teamIds[$index] ?? null, // Assign matching team_id
    ], array_keys($new_memberss));

    DB::table('task_assign_emp')->insert($insertData);
}

    // Delete removed members in bulk
    if(in_array(session('role_id'),config('global.task_monitor_roles'))){
     if (!empty($old_members_remove) && !empty($other_team_mem)) {
         $members_to_remove = array_diff($old_members_remove, $other_team_mem); // Get only values NOT in $other_team_mem
            if (!empty($members_to_remove)) { 
                    TaskAssignEmp::where('task_id', $request->taskId)
                                        ->whereIn('employee_id', $members_to_remove)
                                        ->delete();
            }
        } } else {
             if (!empty($old_members_remove)) {
                    TaskAssignEmp::where('task_id', $request->taskId)
                                        ->whereIn('employee_id', $old_members_remove)
                                        ->delete();
        }  
        }  
         
    return response()->json([
        'message' => 'Task employees updated successfully!'
    ], 200);
}
public function load_members_assigned($task_id){
            $tasks = Tasks::with(['assignedTeams','assignedMembers'])->findOrFail($task_id);
            $member_info='';
            $empId = session('user_id');
            $ctrlStatus = config('global.ctrl_status');

            $datas['cntrl_teams'] = TaskAssignTeam::where('task_id', $task_id)
                ->whereHas('teamMembers', function ($query) use ($ctrlStatus, $empId) {
                    $query->where('ctrl_status', $ctrlStatus)
                        ->where('emp_id',$empId);
                })
                ->pluck('team_id')->toArray();


            foreach ($tasks->assignedMembers as $member) {
            
                $teamMembers = DB::table('task_assign_team as tas')
                ->join('team_members as tm', 'tm.team_id', '=', 'tas.team_id')
                ->where('tas.task_id', $task_id)
                ->where('tm.emp_id',$member->id )
                ->pluck('tas.team_id', 'tm.emp_id')
                ->toArray();
            $teamName = DB::table('teams')->where('id', $member->pivot->team_id)->value('team_name') ?? 'No Team Name';
                $member_info .= '<tr id="emp_' . $member->pivot->id . '"';
                if(!in_array($member->pivot->team_id,session('team_id'))){
                    $member_info .= ' class="faded"';
                    } else {
                        $member_info .= ' class="bright"';
                    }
                
                $member_info .= '>
                                <td><input type="hidden" name="assign_mem_id[]" value="' . $member->id . '">
                                <input type="hidden" class="form-control" name="assign_team_id[' . $member->id . ']" value="' . $member->pivot->team_id . '">
                                ' . $member->name .'-'.$teamName. '</td>
                                <td>';
                $member_info .= '<input type="text"  name="assign_mem_task[' . $member->id . ']" value="' . $member->pivot->task_info . '"';
                if(!in_array($member->pivot->team_id,session('team_id'))){
                $member_info .= 'class="form-control disabledtext" readonly';
                } else {
                    $member_info .= 'class="form-control" ';
                }
                $member_info .='></td>
                                <td>' . (!empty($member->pivot->comments) ? $member->pivot->comments : 'No Comments') . '</td>
                            ';
                    if (
                        (in_array($member->pivot->team_id,session('team_id'))) 
                    ) {
                        //|| (in_array(session('role_id'),config('global.all_in_all_access')))
                        $member_info .= '<td><button type="button" class="btn btn-danger btn-sm delete-btn_task" data-id="' . $member->pivot->id . '">
                                    <i class="fa fa-trash-o"></i>
                                    </button></td>
                                    </tr>';
                    } else {
                    $member_info .= '<td> </td></tr>'; 
                    }

                }                
            return response()->json(['data' => $member_info],200);
 }
    public function get_assigned_teams_members($task_id){
        $retrive_task_emp=TaskAssignEmp::with('team:id,team_name', 'user:id,name')
                                        ->where('task_id',$task_id)
                                        ->where('ctrl_status','!=',config('global.ctrl_status'))
                                        ->whereIn('team_id',session('team_id'))
                                        ->select('employee_id','team_id')
                                        ->get()
                                        ->map(function ($member) {
                                            return [
                                                'employee_id' => $member->employee_id,
                                                'team_id'=>$member->team_id,
                                                'team_name' => $member->team->team_name,
                                                'employee_name' => $member->user->name,
                                            ];
                                        });
        return response()->json($retrive_task_emp);
    }                        

public function assignTask($task_id,$notify_type,$subject,$base_content='',$single_mem_act = null)
{
    //$all_emp_ids = $request->input('employee_ids'); // [ctrl_id => 0 or 1]
    $task = Tasks::findOrFail($task_id);
    $actorUserId = session('user_id');
    if($notify_type=='reopen_tasks'){
        $all_emp_ids=$single_mem_act;
    }else {
        $all_emp_ids = TaskAssignEmp::where('task_id', $task_id)
                                ->pluck('ctrl_status', 'employee_id')
                                ->toArray();
    }
    $pmIds = DB::table('task_assign_emp')
        ->where('task_id',$task_id)
        ->join('teams', 'teams.id', '=', 'task_assign_emp.team_id')
        ->join('team_type', 'team_type.id', '=', 'teams.team_type')
        ->pluck('team_type.pm_id')
        ->unique()
        ->filter()
        ->toArray();
        foreach ($pmIds as $pmId) {
                if (!isset($all_emp_ids[$pmId])) {
                    $all_emp_ids[$pmId] = config('global.ctrl_status'); // or 1 if you want them as reporting
                }
            }
        $assignedEmpIds = array_keys(array_filter($all_emp_ids, fn($v) => $v == 0));
        $reportingEmpIds = array_keys(array_filter($all_emp_ids, fn($v) => $v == 1));

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
        if ($notify_type === 'remove_task_notice'){
            $assignedView = array_filter($assignedView, fn($m) => $m['user_id'] != $single_mem_act['id']);
        }
        $reportingView = collect($reportingEmpIds)
            ->map(fn($id) => $members[$id] ?? null)
            ->filter()
            ->values()
            ->toArray();
        $senderId = session('user_id') ?? config('global.superadmin_id');
        $sender = User::find($senderId);
        if (!$sender) {
            logger()->error('Sender not found in session or DB.');
            // Optionally assign a fallback
            $senderMeta = config('global.admin');
        } else {
        $senderMeta = [
                    'id' => $senderId,
                    'name' => session('user_name') ?? optional($sender)->name,
                    'role' => session('role_name') ?? optional($sender)->roles->pluck('role_name')->first(),
                ];
            }
        foreach ($all_emp_ids as $empId => $roleFlag) {
                    $user = User::find($empId);
                    if (!$user) continue;
                    $isControl = $roleFlag == 1;
                    $isSelfPM = in_array($empId, $pmIds) && session('user_id') == $empId;
                    $senderMeta = $isSelfPM
                                        ? config('global.admin')
                                        : [
                                            'id' => session('user_id'),
                                            'name' => optional(User::find(session('user_id')))->name,
                                            'email' => optional(User::find(session('user_id')))->email,
                                            'role' => optional(optional(User::find(session('user_id')))->role)->name ?? 'User',
                                        ];
                   // logger()->info('Dispatching for', ['emp_id' => $empId, 'isSelfPM' => $isSelfPM]);
                    $assignedForUser = $assignedView;
                    
                    $full_message=$this->storeTaskNotification($user, $task, $assignedForUser, $reportingView, $senderMeta, $subject,$base_content,$notify_type,$single_mem_act,$isControl);                   
                                       
                        dispatch(new SendTaskNotificationMail(
                            $user,         
                            $subject,      
                            $full_message ,
                             $senderMeta['name'] . ' <br/> ' . $senderMeta['role']     
                        ));
                }
                if($notify_type=="remove_task_notice"){
                /**Notify Deleted Member */
            DB::table('notification')->insert([
                'notify_type'   => $notify_type,
                'receiver_id'   => $single_mem_act['id'],
                'sender_id'     => $senderMeta['id'],
                'sender_name'   => $senderMeta['name'] . ' - ' . $senderMeta['role'],
                'receiver_name' => $single_mem_act['name'],
                'is_read'       => config('global.notify_unread'),
                'subject'       => 'Fortgrid - You are removed from the task.',
                'message'       =>  "You have been removed from the task: {$task->task_name}.",
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        $user = User::find($single_mem_act['id']);
                dispatch(new SendTaskNotificationMail(
                            $user,         
                            $subject,      
                            "You have been removed from the task: {$task->task_name}.",
                             $senderMeta['name'] . ' <br/> ' . $senderMeta['role']        
                        ));
                }
    // continue with your controller logic
    return response()->json(['message' => 'Task assigned and notifications dispatched.']);
}
private function storeTaskNotification($receiver, $task, $assignedView, $reportingView, $senderMeta, $subject,$base_content,$notifyType,$single_mem_act='',$excludeSelf = false)
{
    $userId = $receiver->id;

    $buildTable = function ($heading, $members) use ($userId, $excludeSelf) {
        if (empty($members)) return '';

        $rows = collect($members)
            ->reject(fn($m) => $excludeSelf && $m['user_id'] == $userId)
            ->map(fn($m) =>
                "<tr><td>{$m['name']}</td><td>{$m['team_name']}</td><td>{$m['role_name']}</td></tr>"
            )
            ->implode('');

        return "<strong>{$heading}</strong><br>
            <table border='1' cellpadding='5' cellspacing='0'>
                <thead><tr><th>Name</th><th>Team</th><th>Role</th></tr></thead>
                <tbody>{$rows}</tbody>
            </table><br>";
    };

  if ($notifyType === 'remove_task_notice'){
      if($single_mem_act['id']!='' && $single_mem_act['id']!=session('user_id')) {
            $base = "<p>A member is removed from the task: <strong>{$task->task_name}</strong></p>
                    <p>".$base_content['message']."</p>";
        } else {
            $base = "<p>You have been removed from the task: <strong>{$task->task_name}</strong></p>";
            $subject=$base_content['subject'];
        } 
  } else if($notifyType!='reopen_tasks'){
     $base = "<p>You have been assigned to the task: <strong>{$task->task_name}</strong></p>
            <p>Please find the updated details of the members assigned to the task.</p>";
  } else {
    $base = $base_content['message'].
    "<p>Please find the updated details of the members assigned to the task.</p>";
  }

    $message = $base
        . $buildTable('<p>Members Assigned</p>', $assignedView)
        . $buildTable('<p>Reporting Members</p>', $reportingView);
    if ($notifyType != 'remove_task_notice'){
    $message .= "<br/><br/><a href='" . route('tasks.manage_tasks') . "' target='_blank'>Click the Link For more details about the task.</a><br/><br/>";
    }
    DB::table('notification')->insert([
        'notify_type'   => $notifyType,
        'receiver_id'   => $receiver->id,
        'sender_id'     => $senderMeta['id'],
        'sender_name'   => $senderMeta['name'] . ' - ' . $senderMeta['role'],
        'receiver_name' => $receiver->name,
        'is_read'       => config('global.notify_unread'),
        'subject'       => $subject,
        'message'       => $message,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    return $message; // useful for passing to the job for email if needed
}

public function assignTaskStatus($task_id,$notify_type,$subject,$base_content)
{
    //$all_emp_ids = $request->input('employee_ids'); // [ctrl_id => 0 or 1]
    $task = Tasks::findOrFail($task_id);
    $actorUserId = session('user_id');
    $query= TaskAssignEmp::where('task_id', $task_id);
    if($notify_type=='team_status_update'){
         $query->whereIn('team_id',session('team_id'));
    } 
    $all_emp_ids = $query ->pluck('ctrl_status', 'employee_id')
                          ->toArray();
        $query1=DB::table('task_assign_emp')
    ->where('task_id',$task_id)
    ->join('teams', 'teams.id', '=', 'task_assign_emp.team_id')
    ->join('team_type', 'team_type.id', '=', 'teams.team_type');
        if($notify_type=='team_status_update'){
          $query1->whereIn('team_id',session('team_id'));  
        }
$pmIds =$query1 ->pluck('team_type.pm_id')
    ->unique()
    ->filter()
    ->toArray();
        foreach ($pmIds as $pmId) {
                if (!isset($all_emp_ids[$pmId])) {
                    $all_emp_ids[$pmId] = 1; // or 1 if you want them as reporting
                }
            }
        $reportingView = collect($all_emp_ids)
            ->map(fn($id) => $members[$id] ?? null)
            ->filter()
            ->values()
            ->toArray();     
   
 $senderId = session('user_id') ?? config('global.superadmin_id');
 $sender = User::find($senderId);
 $senderMeta = config('global.admin');

foreach ($all_emp_ids as $empId => $roleFlag) {
            $user = User::find($empId);
            if (!$user) continue;
            $isControl = $roleFlag == 1;
            $isSelfPM = in_array($empId, $pmIds) && session('user_id') == $empId;
           // logger()->info('Dispatching for', ['emp_id' => $empId, 'isSelfPM' => $isSelfPM]);
            $message=$this->storeStatusNotification($user, $task,$reportingView, $senderMeta, $subject,$base_content,$notify_type,$isControl);
            
                  dispatch(new SendTaskNotificationMail(
                            $user,         
                            $subject,      
                            $message,
                            $senderMeta['name'] . ' <br/> ' . $senderMeta['role']     
                        ));
        }
    // continue with your controller logic
    return response()->json(['message' => 'Task Status Changed and notifications dispatched.']);
}
    public function proj_tasks(Request $request){
         if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
            if(!in_array(session('role_id'), config('global.restriction_free_roles'))){
                $status['self']="checked";
                $status['other']='';    
            } else {
                $status['self']="";
                $status['other']="checked";
            }
            $LoadDateTimepicker = true;
            $projects = Projects::get();
            $emp_query = User::with('roles')
                    ->where('emp_status', config('global.active_status'))
                    ->where('id', '!=', config('global.superadmin_id'))
                    ->where('id', '!=', session('user_id'))
                    ->whereHas('roles', function($q){
                        $q->whereNotIn('roles.id', config('global.restriction_free_roles'));
                    });
            $employees = $emp_query->get();
            $path = request()->path();
            $LoadDatatables=true; 
        if(in_array(session('role_id'), config('global.restriction_free_roles'))){
            $project_status= ProjectStatus::where('task_set_status',config('global.task_set_status'))->get();
        } else {
            $project_status= ProjectStatus::where('emp_set_status',config('global.task_set_status'))->get(); 
        }
        $task_status=ProjectStatus::where('emp_set_status',config('global.emp_set_status'))->get();
        $view_permit=PermissionHelper::checkPermission('global.categories',$this->view_perm);
        $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
        $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);
       if ($request->ajax()) {
            $userId = session('user_id');
            $statusType = $request->input('status_type');
            $empId = $request->input('emp_id');
            $projectId = $request->input('project_id');
            $moduleId = $request->input('module_id');
            // Safely parse dates with format check
            $startdate = $request->filled('start_date')
                ? Carbon::createFromFormat('d-m-Y', $request->input('start_date'))->format('Y-m-d')
                : null;
            $enddate = $request->filled('end_date')
                ? Carbon::createFromFormat('d-m-Y', $request->input('end_date'))->format('Y-m-d')
                : null;
                $query = PMTasks::with([
                    'project',
                    'pm_task_status',
                    'assignedEmployees.employee',
                    'assignedEmployees.empStatus',
                    'creator'
                ]);
               
                if ($projectId) {
                    $query->where('project_id', $projectId);
                }
                if ($moduleId) {
                    $query->where('module_id', $moduleId);
                }
                if ($startdate && $enddate) {
                    $query->whereBetween('endDate', [$startdate, $enddate]);
                }
                // Apply filters
                $query->whereHas('assignedEmployees.employee', function ($subQuery) {
                    $subQuery->where('emp_status', config('global.active_status'));
                });
                // Restrict by role if needed
                if(!empty($statusType)){
                    if ($statusType == 1) {
                        $query->whereHas('assignedEmployees', function ($subQuery1) use ($userId) {
                            $subQuery1->where('pm_task_assign_emp.employee_id', $userId);
                        });
                    } elseif ($statusType == 2 && $empId) {
                        $query->whereHas('assignedEmployees', function ($subQuery1) use($empId) {
                            $subQuery1->where('pm_task_assign_emp.employee_id', $empId);
                        });
                    }
                    elseif ($statusType == 2) {
                        $query->whereHas('assignedEmployees', function ($subQuery1) use ($userId) {
                            $subQuery1->where('pm_task_assign_emp.employee_id','!=', $userId);
                        });
                    }
                } else {
                     if (!in_array(session('role_id'), config('global.restriction_free_roles'))) {
                        $query->whereHas('assignedEmployees', function ($subQuery1) {
                            $subQuery1->where('pm_task_assign_emp.employee_id', session('user_id'));
                        });
                    }
                }
                // Add select/order
                $query->select('pm_tasks.*')
                    ->orderBy('id', 'desc');

                // Finally execute
                $projects = $query->get();


            // Flatten: one row per assigned employee
           $rows = collect();
        foreach ($projects as $task) {
            if ($task->assignedEmployees->isEmpty()) {
                $rows->push([
                    'id' => $task->id,
                    'task_name' => $task->task_name,
                    'over_all_status_id' =>$task->task_status,
                    'over_all_status_name' =>$task->pm_task_status?->proj_status_name,
                    'task_status' => optional($task->pm_task_status)->proj_status_name,
                    'task_created_by' => optional($task->creator)->name,
                    'deadline' => Carbon::parse($task->endDate)->format('d-m-Y'),
                    'task_assigned_emps' => 'No employees assigned',
                    'task_comments' => null,
                    'task_desc' => $task->task_desc,
                    'project' => optional($task->project)->proj_name,
                    'module_name' => optional($task->modules)->module_name,
                ]);
            } else {
                if (in_array(session('role_id'), config('global.restriction_free_roles'))) {
                    // PM role → one row per employee with their own comment
                    foreach ($task->assignedEmployees as $assign) {
                        $rows->push([
                            'id' => $task->id,
                            'task_name' => $task->task_name,
                            'over_all_status_id' =>$task->task_status,
                            'over_all_status_name' =>$task->pm_task_status?->proj_status_name,
                            'task_status' => optional($assign->empStatus)->proj_status_name,
                            'task_created_by' => optional($task->creator)->name,
                            'deadline' => Carbon::parse($task->endDate)->format('d-m-Y'),
                            'task_assigned_emps' => optional($assign->employee)->name,
                            'task_comments' => $assign->emp_comments,   // ✅ only this employee’s comment
                            'task_desc' => $task->task_desc,
                            'project' => optional($task->project)->proj_name,
                            'module_name' => optional($task->modules)->module_name,
                        ]);
                    }
                } else {
                    // Individual employee → only their own row and comment
                    $assign = $task->assignedEmployees
                        ->where('employee_id', session('user_id'))
                        ->first();

                    $rows->push([
                        'id' => $task->id,
                        'task_name' => $task->task_name,
                        'over_all_status_id' =>$task->task_status,
                        'over_all_status_name' =>$task->pm_task_status?->proj_status_name,
                        'task_status' => optional($assign?->empStatus)->proj_status_name,
                        'task_created_by' => optional($task->creator)->name,
                        'deadline' => Carbon::parse($task->endDate)->format('d-m-Y'),
                        'task_assigned_emps' => optional($assign?->employee)->name,
                        'task_comments' => $assign?->emp_comments,   
                        'task_desc' => $task->task_desc,
                        'project' => optional($task->project)->proj_name,
                        'module_name' => optional($task->modules)->module_name,
                    ]);
                }
            }
        }
            return DataTables::of($rows)
             ->addIndexColumn()
             ->addColumn('overall_task_status', function ($row) {
                        $alert='';
                         if ($row['over_all_status_id'] == config('global.approval_waiting_status')) {
                             $status=' <span class="blinking" title="Deadline">Waiting for Approval</span>';
                        } else {
                            $status=$row['over_all_status_name'];
                        }
                        return $status;
                    })
             ->addColumn('action', function($row) use ($view_permit,$edit_permit,$delete_permit) {
                $editButton = $edit_permit
                    ? '<a href="' . route("tasks.edit", $row['id']) . '" class="btn btn-primary btn-sm" title="Edit Task"><i class="fa fa-edit"></i></a>'
                    : '';

                $changeStateButton = $edit_permit
                                ? '&nbsp;|&nbsp;<button type="button" id="UpdateStat" class="btn btn-danger btn-sm" data-id="'. $row['id'].'" data-bs-placement="top" title="Edit Approval"><i class="fa fa-check" aria-hidden="true"></i></button>' 
                                : '';

                $deleteButton = $delete_permit
                                ? '&nbsp;|&nbsp;<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="'.$row['id'].'"><i class="fa fa-trash-o"></i></button>'
                                : '';
                 $view_button ='';
                 if(!in_array(session('role_id'), config('global.restriction_free_roles'))){
                    $view_button = '<button class="btn btn-primary btn-sm" data-id="'. $row['id'].'" id="EditProjStat"  data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Task Status"><i class="fa fa-edit"></i></button>';
                 $view_button.=' &nbsp;|&nbsp;<button type="button" id="ViewProjStat" class="btn btn-warning btn-sm" data-id="'. $row['id'].'"  data-bs-toggle="tooltip" data-bs-placement="top" title="View Task Information"><i class="fa fa-list-alt"></i></button>';
                 } 
                
                $changeIndivStatButton='';
                    // $mon_role=config('global.restriction_free_roles');
                    // if(in_array(session('role_id'),$mon_role)){
                    //         $changeIndivStatButton ='&nbsp;|&nbsp;<button type="button" id="ChangeStat" class="btn btn-success btn-sm" data-id="'.$row['id'].'"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    // }
                        return $editButton .$changeStateButton . $changeIndivStatButton . $deleteButton.$view_button;
                        //
                    })
                ->rawColumns(['overall_task_status','action'])
                ->make(true);
        }

       return view('tasks.manage_proj_tasks',compact('project_status','task_status','LoadDatatables','status','projects','employees','LoadDateTimepicker'));
    }
        public function add_proj_tasks(){
            $task = new PMTasks();
            $projects = Projects::all();
            if (!PermissionHelper::checkPermission('global.categories', $this->add_perm)) {
                return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
            }
            $LoadMultiselectJS=true;
            $LoadDateTimepicker=true;
            return view('tasks.add_edit_tasks', compact('projects','LoadMultiselectJS','LoadDateTimepicker','task'));
        }
        public function store(Request $request){
                try{
                        $validated = $request->validate([
                            'task_name' =>'required',
                            //'startDate'=> 'required',
                            'endDate'=> 'required',
                            'project_id'=> 'required',
                            'module_id'=> 'required',
                            'task_desc' => 'required',
                            'emp_id.*'=> 'required',
                            'files.*' => 'nullable|file|max:5120',
                        ]);
                        $all_emp_ids=[];
                            $data=$request->only(['task_name', 'project_id', 'module_id','task_desc']);
                        // $data['startDate'] = Carbon::createFromFormat('d-m-Y H:i:s', $request->startDate)->format('Y-m-d H:i:s');
                            $data['endDate'] = Carbon::createFromFormat('d-m-Y', $request->endDate)->format('Y-m-d');
                            $data['created_by']=session('user_id');
                             $task = PMTasks::updateOrCreate(
                                            [
                                                'task_name'  => $data['task_name'],
                                                'project_id' => $data['project_id'],
                                                'module_id'  => $data['module_id'],
                                                'endDate'    => $data['endDate'],
                                            ],
                                            $data
                                        );
                                foreach ($request->emp_id as $emps) {
                                    DB::table('pm_task_assign_emp')->updateOrInsert(
                                                ['employee_id' => $emps, 'task_id' => $task->id],
                                                [] // no extra fields to update
                                            );
                                    $all_emp_ids[$emps]=0;
                                }       
                        $actorUserId = session('user_id');
                        if ($request->hasFile('files')) {
                            foreach ($request->file('files') as $index => $image) {
                                // Capture metadata first
                                    $originalName = $image->getClientOriginalName();
                                    $mimeType     = $image->getClientMimeType();
                                    $size         = $image->getSize();
                                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                                    $image->move(public_path('task_uploads'), $imageName);
                                    // Save metadata in DB
                                    TaskFileUploads::updateOrCreate(
                                        [
                                            'task_id'     => $task->id,
                                            'stored_name' => $imageName,
                                        ],
                                        [
                                            'uploaded_by'   => $actorUserId,
                                            'original_name' => $originalName,
                                            'mime_type'     => $mimeType,
                                            'size'          => $size,
                                        ]
                                    );
                                }
                        }
                            $task_id=$task->id;
                            $notify_type="create_tasks";
                            $subject="Fortgrid - New Task created.";
                            $show_team=true;
                            $this->assignProjTask($task_id,$notify_type,$subject,$show_team);
                            $log_name='tasks';
                            ActivityHelper::logActivity('New Task Created',$log_name, $task, [
                                        'request' => request()->all()
                                    ]);
                            $this->updateProjStatus($request->project_id);
                            return redirect('proj_tasks')->withSuccess('Task Assigned successfully!');
                } catch (\Exception $e) {
                    return response()->json(['message' => $e->getMessage()], 500);
                }
        
        }
        public function edit($id){
            $task = PMTasks::with('assignedEmployees')->findOrFail($id);
            $projects = Projects::all();
            $assignedEmployees = $task->assignedEmployees->pluck('employee_id')->toArray();
            $docs=TaskFileUploads::where('task_id',$id)->get();
            $LoadMultiselectJS=true;
            $LoadDateTimepicker=true;
            $task->endDate=Carbon::parse($task->endDate)->format('d-m-Y');
            return view('tasks.add_edit_tasks', compact('task', 'projects','LoadMultiselectJS','LoadDateTimepicker','assignedEmployees','docs'));
        }
        public function update(Request $request, $id){
                try {
                    $validated = $request->validate([
                        'task_name'   => 'required',
                        'endDate'     => 'required',
                        'project_id'  => 'required',
                        'module_id'   => 'required',
                        'task_desc'   => 'required',
                        'emp_id'      => 'required|array',
                        'emp_id.*'    => 'integer|exists:users,id',
                        'files.*'     => 'nullable|file|max:5120',
                    ]);
                    $task = PMTasks::findOrFail($id);

                    $task->task_name  = $request->task_name;
                    $task->project_id = $request->project_id;
                    $task->module_id  = $request->module_id;
                    $task->task_desc  = $request->task_desc;
                    $task->endDate    = Carbon::parse($request->endDate)->format('Y-m-d');
                    $task->save();

                    // Sync employees without losing statuses
                    $currentEmpIds = DB::table('pm_task_assign_emp')
                        ->where('task_id', $task->id)
                        ->pluck('employee_id')
                        ->toArray();

                    $newEmpIds = $request->emp_id;
                    $newemp_exist=false;
                    $remove_emps=false;
                    foreach ($newEmpIds as $empId) {
                        $newemp_exist=true;
                        if (!in_array($empId, $currentEmpIds)) {
                            DB::table('pm_task_assign_emp')->insert([
                                'employee_id' => $empId,
                                'task_id'     => $task->id,
                            ]);
                        }
                    }
                    if($newemp_exist==true){
                        DB::table('pm_tasks')->where('id',$task->id)->update([
                            'task_status' => config('global.in_progress')
                        ]);
                        $this->updateProjStatus($request->project_id);
                            $task_id=$task->id;
                            $notify_type="modify_tasks_new_mem";
                            $subject="Fortgrid - New  Members Added to Task.".$task->task_name;
                            $show_team=true;
                            $this->assignProjTask($task_id,$notify_type,$subject,$show_team);
                    }
                        

                    $removeEmpIds = array_diff($currentEmpIds, $newEmpIds);
                    if (!empty($removeEmpIds)) {
                        $remove_emps=true;
                        DB::table('pm_task_assign_emp')
                            ->where('task_id', $task->id)
                            ->whereIn('employee_id', $removeEmpIds)
                            ->delete();
                    }
                    if($remove_emps==true){
                        $task_id=$task->id;
                            $notify_type="modify_tasks_remove_mem";
                            $subject="Fortgrid - Modifications in Members Assigned for the Task.".$task->task_name;
                            $show_team=true;
                            $this->assignProjTask($task_id,$notify_type,$subject,$show_team); 
                    }

                    $actorUserId = session('user_id');
                    $attachment_modify=false;
                    if ($request->hasFile('files')) {
                        foreach ($request->file('files') as $image) {
                            $originalName = $image->getClientOriginalName();
                            $mimeType     = $image->getClientMimeType();
                            $size         = $image->getSize();

                            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            $image->move(public_path('task_uploads'), $imageName);

                            TaskFileUploads::create([
                                'task_id'      => $task->id,
                                'uploaded_by'  => $actorUserId,
                                'original_name'=> $originalName,
                                'stored_name'  => $imageName,
                                'mime_type'    => $mimeType,
                                'size'         => $size,
                            ]);
                            $attachment_modify=true;
                        }
                        if($attachment_modify==true){
                             $task_id=$task->id;
                            $notify_type="modify_tasks_attachment";
                            $subject="Fortgrid - Modifications in File Attachment for the  Assigned Task.".$task->task_name;
                            $show_team=false;
                            $this->assignProjTask($task_id,$notify_type,$subject,$show_team);  
                        }
                    }

                    ActivityHelper::logActivity('Task Updated', 'tasks', $task, [
                        'request' => $request->all()
                    ]);
                    $this->updateProjStatus($request->project_id);
                    return redirect('proj_tasks')->withSuccess('Task updated successfully!');
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['error' => $e->getMessage()]);
                }
        }
        public function remove_attachment($item_id){
            try {
               $doc = TaskFileUploads::findOrFail($item_id);
                $filePath = public_path('task_uploads/' . $doc->stored_name);

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                $doc->delete();
                return response()->json([
                    'message' => 'Task Attachment Deleted',
                    'row_id'  => $doc->id
                ], 200);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }
          public function getMappedTasks($proj_id,$module_id){
                $tasks = PMTasks::where('project_id', $proj_id)
                                ->where('module_id', $module_id)
                                ->get()
                                ->mapWithKeys(function ($assign) {
                                    return [$assign->id => $assign->task_name];
                                });

                return response()->json($tasks);
            }
       public function destroy($id){
            $cat_permission = PermissionHelper::checkPermission('global.categories', $this->del_perm);

            if (!$cat_permission) { 
                return response()->json(['message' => 'Not Authorized to see this page.'], 200);
            } else {
                $tasks = PMTasks::find($id);
                    if ($tasks->timesheets()->exists()) {
                        return response()->json(['message' => 'Cannot delete: project has related records.'], 400);
                    } else { 
                        if ($tasks) {
                            TaskFileUploads::where('task_id', $id)->delete();
                            PMTasksAssign::where('task_id', $id)->delete();

                            $log_name = 'projects';
                            ActivityHelper::logActivity('Task Deleted', $log_name, $tasks, [
                                'request' => request()->all()
                            ]);
                            $tasks->delete();

                            return response()->json(['message' => 'Record Deleted successfully!'], 200);
                        }
                    }
                return response()->json(['message' => 'Task not found.'], 404);
            }
}
        public function user_proj_status($id){
            $task_dtls= PMTasksAssign::with('task','empStatus')->where('task_id', $id)
                                    ->where('employee_id', session('user_id'))
                                    ->first(); 
            return response()->json($task_dtls) ;  
        }
        public function getAssignedMembersInfoPmProjects($task_id){
            $task_dtls= PMTasksAssign::with('employee','empStatus')->where('task_id', $task_id)
                        ->where('employee_id','!=', session('user_id'))
                        ->get();
            $attachments=TaskFileUploads::where('task_id', $task_id)->get();
               return response()->json([
                                        'tasks' => $task_dtls,
                                        'attachments' => $attachments
                                        ]);
        }
        public function task_ind_update_pm_tasks(Request $request){
            /**Update Status as Request */
            $task_dtls= DB::table('pm_task_assign_emp')
                            ->where('task_id',$request->task_id)
                            ->where('employee_id',session('user_id'))
                            ->update([
                                        'emp_task_status'=>$request->indiv_proj_status,
                                        'emp_comments' => $request->comments,
                                    ]);
                $log_name='pm_tasks';
                ActivityHelper::logActivity('Task Status Updated Individually',$log_name, $task_dtls, [
                            'request' => request()->all()
                        ]);
                
            /**If TASK STARTED BY ALL MEMBERS IF SET AS COMPLETED OVERALL TASK STATUS IS SET AS COMPLETED.*/
            $allCompleted = PMTasksAssign::where('task_id', $request->task_id)
                                            ->where('emp_task_status', $request->indiv_proj_status)
                                            ->count() == 0;
             $distinctStatuses = PMTasksAssign::where('task_id', $request->task_id)->where('emp_task_status', $request->indiv_proj_status)
                                ->pluck('emp_task_status')
                                ->unique();
                $allCompleted=$distinctStatuses->count();
                    if ($allCompleted==1) {
                        if( $request->indiv_proj_status!= config('global.completed_status')){
                             $current_task= PMTasks::where('id', $request->task_id)
                                        ->update([
                                            'task_status' => $request->indiv_proj_status
                                        ]);
                        } else {
                            $current_task= PMTasks::where('id', $request->task_id)
                                        ->update([
                                            'task_status' => config('global.approval_waiting_status')
                                        ]);
                        }       
                        $log_name='tasks';
                        ActivityHelper::logActivity('Task Update  by all team members',$log_name, $current_task, [
                                    'request' => request()->all()
                                ]);
                          $notify_type='task_status_update';
                                    $subject='Fortgrid- Task Waiting For Approval';
                                    $show_team=false;
                            $this->assignProjTask($request->task_id,$notify_type,$subject,$show_team);
                    } else {
                          $current_task= PMTasks::where('id', $request->task_id)
                                        ->update([
                                            'task_status' =>  config('global.in_progress')
                                        ]);
                    }
                    /**If TASK STARTED BY ALL MEMBERS IF SET AS COMPLETED OVERALL TASK STATUS IS SET AS COMPLETED.*/
                    
                return response()->json(['message' => 'Tasks Updated successfully!.','task' => $current_task],200);
}
public function get_pm_tasks_info($id){
    $result=PMTasks::where('id',$id)->select('task_status','comments')->first();
    // $reopen_tsk=ReopenedTask::where('task_id',$id)
    //                        ->where('ctrl_status','!=',config('global.ctrl_status'))
    //                         ->whereIn('team_id',session('team_id'))
    //                         ->select('task_id', 'ctrl_status','reopen_type', DB::raw("GROUP_CONCAT(emp_id SEPARATOR ', ') as emp_ids"))
    //                         ->groupBy('task_id', 'ctrl_status','reopen_type')
    //                         ->first(); 
    //,'reopen_tsk' =>$reopen_tsk
     return response()->json(['task' => $result],200);
}

public function task_update_pm_status(Request $request){
    try{
    $task_dtls=PMTasks::find($request->recordTaskMainId);
    $task_dtls->update([
            'task_status'=>$request->task_status,
            'comments'=>$request->comments
        ]);
      $projectId = $task_dtls->project_id;
      $log_name='tasks';
      ActivityHelper::logActivity('Task Status Updated - Main Task.',$log_name, '', [
                  'request' => request()->all()
              ]);
                $notify_type='task_status_update_pm';
                $subject='Fortgrid- Overall Task Status Changed';
                $show_team=false;
                            $this->assignProjTask($request->recordTaskMainId,$notify_type,$subject,$show_team);
              /**Check All tasks assigned for the project is completed or not if yes Update project as completed or else change to Inprogress */
              $this->updateProjStatus($projectId);
            return response()->json(['success' => 'Task Status Updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
}
public function updateProjStatus($projectId){
       /**Check All tasks assigned for the project is completed or not if yes Update project as completed or else change to Inprogress */
           $allCompleted = PMTasks::where('project_id', $projectId)
                            ->where('task_status', '!=', config('global.completed_status'))
                            ->doesntExist();   
                        if ($allCompleted) {
                            Projects::where('id', $projectId)
                                ->update(['proj_status' => config('global.completed_status')]);
                        } else {
                            Projects::where('id', $projectId)
                                ->update(['proj_status' => config('global.in_progress')]);
                        }
}
public function assignProjTask($task_id,$notify_type,$subject,$show_team)
{
    $all_emp_ids=[];
    $receiver_ids=[];
    $tasks = PMTasks::where('id',$task_id)->get();
        foreach ($tasks as $task) {
           foreach ($task->assigned_employee_details as $emp) {
                $all_emp_ids[$emp['id']] = [
                    'id'    => $emp['id'],
                    'name'  => $emp['name'],
                    'email' => $emp['email'],
                    'role' =>  $emp['role'],
                ];
            }
        }
        if($notify_type =='task_status_update' || $notify_type=="task_status_update_pm"){
            $emp_query = User::with('roles')
                        ->where('emp_status', config('global.active_status'))
                        ->where('id', '!=', config('global.superadmin_id'))
                        ->where('id', '!=', session('user_id'))
                        ->whereHas('roles', function($q){
                            $q->whereIn('roles.id', config('global.task_approve_roles'));
                        });
            $employees = $emp_query->get();
            foreach ($employees as $emp){
                 $receiver_ids[$emp->id] = [
                    'id'    => $emp->id,
                    'name'  => $emp->name,
                    'email' =>  $emp->email,
                    'role' =>  $emp->roles->first()->role_name,
                ];
            }
            $senderMeta = config('global.admin');
        }
        if($notify_type=="create_tasks" || $notify_type=="modify_tasks_new_mem" || $notify_type=="modify_tasks_remove_mem" || $notify_type=="task_status_update_pm"){
           $receiver_ids = array_merge($receiver_ids, $all_emp_ids);
              $senderMeta =  [
                                    'id' => session('user_id'),
                                    'name' => session('user_name') ,
                                    'email' => session('email'),
                                    'role' => session('role_name') ?? 'User',
                                ];
        }
                    $full_message=$this->storeProjTaskNotification($all_emp_ids, $tasks, $senderMeta, $subject,$notify_type,$show_team,$receiver_ids);
                    $assignedMembers = $all_emp_ids;


                           dispatch(new SendPMTaskNotificationMail(
                                    $assignedMembers,   // full list
                                    $subject,
                                    $full_message,
                                    $senderMeta,
                                    $show_team,
                                    $receiver_ids
                                ));
    return response()->json(['message' => 'Task assigned and notifications dispatched.']);
}
private function storeProjTaskNotification($team_cols, $task, $senderMeta, $subject,$notifyType,$show_team,$receiver_ids)
{
    $tasks='';
     $tasks=$task[0];
        $base="";
    if($notifyType=="create_tasks"){
        $heading='Task Created and Assigned Members Details:';
    } 
    
    if($notifyType=='modify_tasks_new_mem'){
        $heading='Task Details Edited and New Members Assigned. <br/><br/>';
    } 

     if($notifyType=='modify_tasks_remove_mem'){
        $heading='Task Details Edited and Modifications in Members Assigned for the Task. <br/><br/>';
    } 

     if($notifyType=='modify_tasks_attachment'){
        $heading='Task Details Edited and Modifications in Reference Attachment Files for the Task Assigned.';
        $base.="<strong>{$heading}</strong><br/><br/> <b>Task Name:".$tasks->task_name."</b><br/><br/>. New Reference Attachments have been added to the Task. Please Visit HRMS Site to download the Attachment.";
    } 
     if($notifyType=='task_status_update'){
        $heading='Task Waiting For Approval-'.$tasks->task_name;
        $base.='<p>All members Assigned for the task "'.$tasks->task_name.'" has completed the task. Waiting for Approval to complete the task.The Details are as follows:</p>';
    }
     if($notifyType=='task_status_update_pm'){
        $heading='Over All Task Status Changed-'.$tasks->task_name;
        $base.='<p>Task Status Changed as '.$tasks->task_status.'The Details are as follows:</p>';
        $base.='<p>Comments: '.$tasks->comments.'</p>';
    }
 $base.="<b>Task Name:".$tasks->task_name."</b><br/><br/>";
                $msg_notify='';
    if($show_team==true) {
        $msg_notify.="<b>Assigned Members Details are as Follows:</b><br/><br/>
                        <table border='1' cellpadding='5' cellspacing='0'>
                            <thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead>
                            <tbody>";
                foreach($team_cols as $receiver){
                    $userId = $receiver['id'];
                    $msg_notify.="<tr><td>".$receiver['name']."</td><td>".$receiver['email']."</td><td>".$receiver['role']."</td></tr>";
                }
        $msg_notify.="</tbody> </table><br>";
    }
    $message = $base;
    if ($notifyType != 'remove_task_notice'){
     $notify_lnk = "<br/><br/><a href='" . route('tasks.proj_tasks') . "' target='_blank'>Click the Link For more details about the task.</a><br/>";
    }  
     foreach($receiver_ids as $receiver){
    DB::table('notification')->insert([
        'notify_type'   => $notifyType,
        'receiver_id'   => $receiver['id'],
        'sender_id'     => $senderMeta['id'],
        'sender_name'   => $senderMeta['name'] . ' - ' . $senderMeta['role'],
        'receiver_name' => $receiver['name'],
        'is_read'       => config('global.notify_unread'),
        'subject'       => $subject,
        'message'       => $message.$msg_notify.$notify_lnk,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
     }
    return $message; // useful for passing to the job for email if needed
}
}