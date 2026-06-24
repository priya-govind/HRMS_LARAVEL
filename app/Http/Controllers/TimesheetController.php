<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timesheet;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Projects;
use App\Models\ProjectModuleAssign;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Models\Leaveinfo;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Exports\TimesheetExport;
use App\Jobs\SendTimesheetReportJob;



class TimesheetController extends Controller
{
   public function fillup_sheet(){
      if (!PermissionHelper::checkPermission('global.categories', $this->add_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        } 
    if ((int)session('incomplete_timesheet') === 1){
    $date = Carbon::createFromFormat('d-m-Y', session('last_checkin_dt'))->format('Y-m-d');
    $cur_date=Carbon::createFromFormat('d-m-Y', session('last_checkin_dt'))->format('d M,Y');
    } else {
    $date = date('Y-m-d');
    $cur_date=date('d M,Y');
    }
      $loadSelect2JS=true;
      $savedSlots=Timesheet::where('emp_id', session('user_id'))
                                 ->where('create_dt',$date)
                                 ->get();
    $permissionSlots = Leaveinfo::where('emp_id', session('user_id'))
    ->whereDate('from_dt', Carbon::today())
    ->where('leave_status', 1)
    ->select('from_time', 'to_time')
    ->get()
    ->map(function ($slot) {
        return [
            'from_time' => $slot->from_time,
            'to_time' => $slot->to_time,
        ];
    });
       return view('timeslot.timesheet',compact('loadSelect2JS','savedSlots','permissionSlots','cur_date','date'));
   }
   public function index(){
    
      $date = date('Y-m-d');
      $loadSelect2JS=true;
      $savedSlots=Timesheet::where('emp_id', session('user_id'))
                                 ->where('create_dt',$date)
                                 ->get();
        $permissionSlots = Leaveinfo::where('emp_id', session('user_id'))
            ->whereDate('from_dt', Carbon::today())
            ->where('leave_status', 1)
            ->select('from_time', 'to_time')
            ->get()
            ->map(function ($slot) {
                return [
                    'from_time' => $slot->from_time,
                    'to_time' => $slot->to_time,
                ];
            });
       return view('timeslot.timesheet',compact('loadSelect2JS','savedSlots','permissionSlots'));
   }
   public function view_timesheet($prev_date,$user_id=''){
            $date = $prev_date ?? now()->toDateString();
        $isToday = $date === now()->toDateString();
            $user_id=$user_id ? $user_id :  session('user_id');
            $user_name=$user_id ? User::where('id',$user_id)->pluck('name')->toArray()[0] :  session('user_name');
            
            $slot_date=Carbon::createFromFormat('Y-m-d', $date)->format('d M,Y');  
            //$loadSelect2JS=true;
            $savedSlots=Timesheet::where('emp_id', $user_id)
                                        ->where('create_dt',$date)
                                        ->orderBy('id', 'asc')
                                        ->get();
            $permissionSlots = Leaveinfo::where('emp_id', $user_id)
                    ->whereDate('from_dt', Carbon::today())
                    ->where('leave_status', 1)
                    ->select('from_time', 'to_time')
                    ->get()
                    ->map(function ($slot) {
                        return [
                            'from_time' => $slot->from_time,
                            'to_time' => $slot->to_time,
                        ];
                    });
            return view('timeslot.view_timesheet',compact('savedSlots','permissionSlots','date','isToday','user_name','slot_date'));
   }
   public function edit_timesheet($id){
    $fetch_info=Timesheet::where('id',$id)->select('create_dt')->first()->toArray();
    $date = $fetch_info['create_dt'];
  
    $user_id=session('user_id');
    $user_name=session('user_name');
    
    $slot_date=Carbon::createFromFormat('Y-m-d', $date)->format('d M,Y');  
    $loadSelect2JS=true;
      $savedSlots=Timesheet::where('emp_id', $user_id)
                                 ->where('create_dt',$date)
                                 ->get();
      $permissionSlots = Leaveinfo::where('emp_id', $user_id)
            ->whereDate('from_dt', $date)
            ->where('leave_status', 1)
            ->select('from_time', 'to_time')
            ->get()
            ->map(function ($slot) {
                return [
                    'from_time' => $slot->from_time,
                    'to_time' => $slot->to_time,
                ];
            });
       return view('timeslot.edit_timesheet',compact('loadSelect2JS','savedSlots','permissionSlots','date','user_name','slot_date'));
   }
   public function checkPermissionSlot(Request $request)
{
    $fromTime = Carbon::parse($request->from_time);
    $toTime = Carbon::parse($request->to_time);

    $permissionExists = Leaveinfo::where('emp_id', session('user_id'))
        ->where('leave_type', config('global.leave_type_permission'))
        ->whereDate('from_dt', Carbon::today())
        ->where('leave_status',config('global.leave_approved'))
        ->where(function ($query) use ($fromTime, $toTime) {
            $query->where(function ($q) use ($fromTime, $toTime) {
                $q->whereTime('from_time', '<', $toTime)
                  ->whereTime('to_time', '>', $fromTime);
            });
        })
        ->exists();

    return response()->json(['blocked' => $permissionExists]);
}
public function store(Request $request)
{
    try {
        $rules = [
            'create_dt'   => 'required|date',
            'comments'    => 'required|string',

            'project_id'  => 'required_without_all:custom_project|nullable|string',
            'custom_project' => 'required_without_all:project_id|nullable|string',

            'module_id'   => 'required_without_all:custom_module|nullable|string',
            'custom_module' => 'required_without_all:module_id|nullable|string',

            'task_id'     => 'required_without_all:custom_task|nullable|string',
            'custom_task' => 'required_without_all:task_id|nullable|string',
        ];

        if (!$request->filled('id')) {
            $rules['from_time'] = 'required';
            $rules['to_time']   = 'required';
        }
        $validated = $request->validate($rules);
        $date = Carbon::parse($validated['create_dt'])->format('Y-m-d');

        if ($request->filled('id')) {
            // Edit flow
            $timesheet = Timesheet::findOrFail($request->id);
                 $durationMinutes = Carbon::createFromFormat('g:i A', $timesheet->from_time)
                                ->diffInMinutes(Carbon::createFromFormat('g:i A',  $timesheet->to_time), false);
            $timesheet->update([
                'comments'    => $validated['comments'],
                'custom_task' => ($request->task_id === 'other'  || $request->task_id === null) ? $request->custom_task : null,
                'custom_project' => ($request->project_id === 'other'  || $request->project_id === null) ? $request->custom_project : null,
                'custom_module' => ($request->module_id === 'other' || $request->module_id === null) ? $request->custom_module : null,
                'project_id'  => ($request->project_id === 'other' || $request->project_id === null )? null :  $validated['project_id'],
                'module_id'   => ($request->module_id === 'other' || $request->module_id === null) ? null : $validated['module_id'],
                'task_id'     => ($request->task_id === 'other' || $request->task_id === null) ? null : $validated['task_id'],
                'duration'      => floor($durationMinutes)
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Timesheet Entry Updated.',
                'lock'    => false,
            ]);
        } else {
            // Add flow
            $fromTime = Carbon::parse($validated['from_time'])->format('g:i A');
            $toTime   = Carbon::parse($validated['to_time'])->format('g:i A');

            // Conflict check
            $conflict = Timesheet::where('emp_id', session('user_id'))
                ->where('create_dt', $date)
                ->where(function($q) use ($fromTime, $toTime) {
                    // $q->where('from_time', '<', $toTime)
                    //   ->where('to_time', '>', $fromTime);
                    $q->where('from_time', '=', $toTime)
                      ->where('to_time', '=', $fromTime);
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'This time overlaps with an existing entry.'
                ]);
            }
            if($validated['from_time']=='9:00 AM' && $date==now()->format('Y-m-d')){
                 $exact_fromTime = (Carbon::parse(session('chkin_time'))->format('g:i A')) ;
                 $fromTime= $exact_fromTime;
            } else {
                 $exact_fromTime = (Carbon::parse($validated['from_time'])->format('g:i A'));
                 $fromTime=$fromTime;
            }
           
            $durationMinutes = Carbon::createFromFormat('g:i A', $exact_fromTime)
                ->diffInMinutes(Carbon::createFromFormat('g:i A', $toTime), false);

           $timesheet = Timesheet::updateOrCreate(
                [
                    'emp_id'    => session('user_id'),
                    'create_dt' => $date,
                    'from_time' => $fromTime,
                    'to_time'   => $toTime,
                ],
                [
                    // 🔄 These are the values to update if found, or insert if not
                    'project_id'    => $request->project_id === 'other' ? null : $validated['project_id'],
                    'module_id'     => $request->module_id === 'other' ? null : $validated['module_id'],
                    'day'           => Carbon::parse($date)->dayName,
                    'comments'      => $validated['comments'],
                    'task_id'       => $request->task_id === 'other' ? null : $validated['task_id'],
                    'custom_task'   => $request->task_id === 'other' ? $request->custom_task : null,
                    'custom_project'=> $request->project_id === 'other' ? $request->custom_project : null,
                    'custom_module' => $request->module_id === 'other' ? $request->custom_module : null,
                    'duration'      => floor($durationMinutes),
                ]
            );

            // Lock if last slot
            if ($timesheet->to_time === '18:00:00') {
                return response()->json([
                    'success' => true,
                    'lock'    => true,
                    'message' => 'Day completed, no more entries allowed.'
                ]);
            }

            // Next slot calculation
            $fromSlots = ["9:00 AM","10:00 AM","11:00 AM","12:00 PM","1:00 PM","1:30 PM","2:00 PM","3:00 PM","4:15 PM","5:00 PM"];
            $toSlots   = ["10:00 AM","10:45 AM","12:00 PM","1:00 PM","1:30 PM","2:00 PM","3:00 PM","4:00 PM","5:00 PM","6:00 PM"];

            $lastFrom = Carbon::parse($timesheet->from_time)->format('g:i A');
            $index    = array_search($lastFrom, $fromSlots);

            $nextFrom = $index !== false && $index < count($fromSlots) - 1 ? $fromSlots[$index + 1] : null;
            $nextTo   = $index !== false && $index < count($toSlots) - 1 ? $toSlots[$index + 1] : null;

            return response()->json([
                'success'   => true,
                'last_from' => $lastFrom,
                'last_to'   => Carbon::parse($timesheet->to_time)->format('g:i A'),
                'next_from' => $nextFrom,
                'next_to'   => $nextTo,
                'message'   => 'Timesheet Entry Added.',
                'lock'      => false,
                 'duration'      => $durationMinutes,
            ]);
        }
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}


public function update_slotcnt($cnt)
{
    $chkin_date = session('last_checkin_dt')
                    ? Carbon::parse(session('last_checkin_dt'))->format('Y-m-d')
                    : Carbon::today()->format('Y-m-d');
    $attendance = Attendance::where('emp_id', session('user_id'))
                            ->whereDate('chkinDate', $chkin_date)
                            ->first();
         if($attendance && session('incomplete_timesheet')==1){
            session(['incomplete_timesheet'=> 0]);
            session(['irregular_chkout'=> 0]);
             return response()->json([
                                    'message' => 'TimeSheet gets AutoSaved Successfully.',
                                    'redirect_url' => '/dashboard'
                                    ]);
        } else if ($attendance) {
        $attendance->update(['timesheet_slot' => $cnt]);
        return response()->json([
            'message' => 'TimeSheet gets AutoSaved Successfully.',
            'redirect_url' => '/attendance_info'
        ]);
    } else {
        return response()->json([
            'message' => 'Attendance record not found.',
            'redirect_url' => '/dashboard'
        ], 404);
    }
}
    public function timesheet_fetch(Request $request){
            $date = $request->query('date');
            $empId = $request->query('emp_id');

            $tasks = TimeSheet::with('Projects')
                        ->whereDate('create_dt', $date)
                        ->where('emp_id', $empId)
                        ->get();
            $task_col=[];
            foreach( $tasks as $tas){
                 $from_time=Carbon::createFromFormat('g:i A', $tas->from_time)->format('h:i A');
                  $to_time=Carbon::createFromFormat('g:i A', $tas->to_time)->format('h:i A');
                $task_col[]=['day' =>  $tas->day,
                        'timings'=>  $from_time.' - '.$to_time,
                        'project' => $tas->Projects->proj_name,
                        'module' =>  $tas->module,
                        'create_dt' =>$tas->create_dt,
                        'description' =>  Str::limit($tas->comments, 20),          
                        ];
            }
            return response()->json($task_col);
    }
    public function getTimesheetDates(){
        $currentDate = now()->format('d-m-Y');

        $previousDate = Timesheet::where('emp_id', session('user_id'))
            ->whereDate('create_dt', '<', now()->format('Y-m-d'))
            ->orderBy('create_dt', 'desc')
            ->value('create_dt');
            
        return response()->json([
            'current_date' => $currentDate,
            'previous_date' => $previousDate ? Carbon::parse($previousDate)->format('d-m-Y') : null,
        ]);
    }

    public function timesheet_log(){
            if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
                return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
            }
            
            $DisableTimesheet=false;
            $LoadDateTimepicker = true;
            $LoadDatatables = true;

            $team_ids = session('team_id');
            $emp_query = User::with('roles')
                ->where('emp_status', config('global.active_status'))
                 ->whereHas('roles', function ($query) {
                          $query->whereNotIn('roles.id', config('global.restriction_free_roles'));
                      })
                ->where('id', '!=', session('user_id'));
            $employees = $emp_query->get();
            $reporting_emps=User::with('roles')
                ->where('emp_status', config('global.active_status'))
                 ->whereHas('roles', function ($query) {
                          $query->whereIn('roles.id', config('global.task_approve_roles'));
                      })
                ->where('id', '!=', session('user_id'));
            $reporting_emps=$reporting_emps->get();
            if(in_array(session('role_id'),config('global.task_approve_roles'))){
                $projects = Projects::pluck('proj_name','id')->all();
            } else {
                $projects = ProjectModuleAssign::with('projects')->where('emp_id',session('user_id'))
                            ->get()
                            ->pluck('projects.proj_name','projects.id')
                            ->unique();
            }
            $employeeId = session('user_id');
            $today = now()->toDateString();

            $fromSlots = ["9:00 AM","10:00 AM","11:00 AM","12:00 PM","1:00 PM","1:30 PM","2:00 PM","3:00 PM","4:15 PM","5:00 PM"];
            $fromdispSlots = ["9:00 AM","10:00 AM","11:00 AM","12:00 PM","1:00 PM","1:30 PM","2:00 PM","3:00 PM","4:00 PM","4:15 PM","5:00 PM"];
            $toSlots   = ["10:00 AM","10:45 AM","12:00 PM","1:00 PM","1:30 PM","2:00 PM","3:00 PM","4:00 PM","5:00 PM","6:00 PM"];

            $entries = Timesheet::where('emp_id', $employeeId)
                ->where('create_dt', $today)
                ->orderBy('from_time')
                ->get();

            if ($entries->isNotEmpty()) {
                // Continue after last entry
                $lastEntry = $entries->last();
                $lastTo    = Carbon::parse($lastEntry->to_time)->format('g:i A');
                $defaultFrom = collect($fromSlots)->first(fn($slot) => strtotime($slot) >= strtotime($lastTo));
            } else {
                // First entry of the day → base on check-in
                $chkinTime = Carbon::parse(session('chkin_time'))->format('g:i A');

                // Step 1: find the last slot <= check-in
                $defaultFrom = collect($fromSlots)->last(function($slot) use ($chkinTime) {
                    return strtotime($slot) <= strtotime($chkinTime);
                });

                // Step 2: if check-in is >= 45 minutes past that slot, bump to next slot
                if ($defaultFrom) {
                    $diffMinutes = (strtotime($chkinTime) - strtotime($defaultFrom)) / 60;
                    if ($diffMinutes >= 45) {
                        $defaultFrom = collect($fromSlots)->first(fn($slot) => strtotime($slot) > strtotime($defaultFrom));
                    }
                }

                // Fallback if nothing matched
                if (!$defaultFrom) {
                    $defaultFrom = collect($fromSlots)->first();
                }
            }
        /**Check whether User entred all the entries if yes block add new timesheet option */
        $today = now()->toDateString();
         $entries = Timesheet::where('emp_id', session('user_id'))
                ->where('create_dt', $today)
                ->orderBy('id','desc')
                ->first();
               
            if (!empty($entries->to_time) && $entries->to_time === '18:00:00') {
            $DisableTimesheet=true;
            } 
            $isEditing=request()->has('edit');
            // Default To = first slot greater than defaultFrom
            $defaultTo = collect($toSlots)->first(fn($slot) => strtotime($slot) > strtotime($defaultFrom));
              $LoadMultiselectchkbox=true;
            return view('timeslot.timesheet_history', compact(
                'LoadDateTimepicker',
                'LoadDatatables',
                'employees',
                'reporting_emps',
                'projects',
                'fromSlots',
                'fromdispSlots',
                'toSlots',
                'defaultFrom',
                'defaultTo',
                'DisableTimesheet',
                'isEditing','LoadMultiselectchkbox'
            ));
    }
    public function timesheet_log_action(Request $request){
                $emp_id='';
                $from_date = $request->input('FromDate');
                $to_date = $request->input('ToDate');
                if(!empty($request->input('emp_id')) && $request->status_type=='2'){
                        $emp_id=  $request->input('emp_id');
                } 
                $query = Timesheet::with('Projects');
                if($request->status_type=='2'){
                    $query->where('emp_id', '!=', session('user_id'));
                } else {
                    $query->where('emp_id',  session('user_id'));
                }
                if(!empty($emp_id) && $request->status_type=='2'){
                    $query->where('emp_id',  $emp_id);
                } 
                 if(!empty($request->proj_id)){
                    $query->where('project_id', $request->proj_id);
                }
                if(!empty($request->module_id)){
                    $query->where('module_id', $request->module_id);
                }
                if ($from_date && $to_date) {
                    $from = Carbon::createFromFormat('d-m-Y', $from_date)->format('Y-m-d');
                    $to = Carbon::createFromFormat('d-m-Y', $to_date)->format('Y-m-d');
                } else{
                    $from = Carbon::now()->startOfWeek()->format('Y-m-d');
                    $to = Carbon::now()->endOfWeek()->format('Y-m-d');
                }
                    try {
                    if(!empty($request->input('FromDate')) && !empty($request->input('ToDate'))){
                         $query->whereBetween('create_dt', [$from, $to]);
                    } else {
                        $query->where('create_dt', now()->format('Y-m-d'));
                    }                      
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Invalid date format.'], 422);
                    }
                $history = $query->orderBy('create_dt', 'desc')->get();
               $totalMinutes = $history->sum('duration'); 
                $get_dts=$this->getTimesheetDates()->getData(true);
                return DataTables::of($history)
                    ->addIndexColumn()
                    ->editColumn('create_dt', fn($row) => Carbon::parse($row->create_dt)->format('d-m-Y'))
                    ->addColumn('day', fn($row) => Carbon::parse($row->create_dt)->format('l'))
                     ->addColumn('emp_name', function($row) {
                                    return $row->employee->name;
                                })
                    ->addColumn('project_name',function($row) {
                                    if ($row->Projects) {
                                        return optional($row->Projects)->proj_name;
                                    }
                                    return $row->custom_project ?? 'N/A';
                                })
                    ->addColumn('module',function($row) {
                                    if ($row->module) {
                                        return optional($row->module)->module_name;
                                    }
                                    return $row->custom_module ?? 'N/A';
                                })
                   ->addColumn('task', function($row) {
                                    if ($row->task) {
                                        return $row->task->task_name;
                                    }
                                    return $row->custom_task ?? 'N/A';
                                })
                     ->addColumn('duration_minutes', fn($row) => $row->duration)
                    ->addColumn('start_time', fn($row) =>Carbon::createFromFormat('g:i A', $row->from_time)->format('h:i A'))
                    ->addColumn('end_time', fn($row) => Carbon::createFromFormat('g:i A', $row->to_time)->format('h:i A'))
                    ->addColumn('worked_time', function($row) {
                               $minutes = $row->duration;
                                    if ($minutes < 60 && $minutes>0) {
                                        $duration = $minutes . ' mins';
                                    } else if ($minutes == 0) { 
                                        $duration = '-';
                                    } else {
                                        $hours = floor($minutes / 60);
                                        $remainingMinutes = $minutes % 60;

                                        if ($remainingMinutes > 0) {
                                            $duration = $hours . ' hr ' . $remainingMinutes . ' mins';
                                        } else {
                                            $duration = $hours . ' hr';
                                        }
                                    }
                            return $duration;
                                })
                    ->addColumn('timesheet_dtls', function ($row) use ($request,$get_dts) {
                        $cur_date = Carbon::parse(now())->format('Y-m-d');
                     
                        if(($row->create_dt==$cur_date || $row->create_dt==Carbon::parse($get_dts['previous_date'])->format('Y-m-d')) && $request->status_type!='2'  && $row->editable==1 ){
                            return "<a class='edit-timesheet btn btn-primary' data-id='".$row->id."' target='_blank' title='Edit Details'><i class='fa fa-file-edit'></i></a>";
                        } else {
                            return "<a class='view-timesheet btn btn-warning' data-id='".$row->id."' target='_blank' title='View Details'><i class='fa fa-file-text'></i></a>";
                        }
                    })
                    ->rawColumns(['timesheet_dtls'])
                     ->with('grand_total_minutes', $totalMinutes)
                    ->make(true);
    }
    public function timesheet_search(Request $request){
         $query = Timesheet::with('Projects');
            $emp_id=$request->emp_id;
                if(!empty($emp_id)){
                    $query->where('emp_id',  $emp_id);
                } 
                if(!empty($request->project_id)){
                    $query->where('project_id', $request->project_id);
                }
                $history = $query->orderBy('create_dt', 'desc')->latest()->limit(5)->get();
                 return DataTables::of($history)
                    ->addIndexColumn()
                     ->addColumn('emp_name', function($row) {
                                    return $row->employee->name;
                                })
                    ->addColumn('project_name',function($row) {
                                    if ($row->Projects) {
                                        return optional($row->Projects)->proj_name;
                                    }
                                    return $row->custom_project ?? 'N/A';
                                })
                    ->addColumn('module',function($row) {
                                    if ($row->module) {
                                        return optional($row->module)->module_name;
                                    }
                                    return $row->custom_module ?? 'N/A';
                                })
                   ->addColumn('task', function($row) {
                                    if ($row->task) {
                                        return $row->task->task_name;
                                    }
                                    return $row->custom_task ?? 'N/A';
                                })
                    ->addColumn('worked_time', function($row) {
                               $minutes = $row->duration;
                                    if ($minutes < 60) {
                                        $duration = $minutes . ' mins';
                                    } else {
                                        $hours = floor($minutes / 60);
                                        $remainingMinutes = $minutes % 60;

                                        if ($remainingMinutes > 0) {
                                            $duration = $hours . ' hr ' . $remainingMinutes . ' mins';
                                        } else {
                                            $duration = $hours . ' hr';
                                        }
                                    }
                            return $duration;
                                })
                    ->make(true);
    }
public function checkEntry(Request $request){


  $date = Carbon::createFromFormat('d-m-Y', $request->date)
                  ->format('Y-m-d');

    $from = trim($request->from_time);
    $to   = trim($request->to_time);

    $entry = Timesheet::where('emp_id', session('user_id'))
        ->where('create_dt', $date)
        ->where('from_time', $from)
        ->where('to_time', $to)
        ->first();
    // $date = Carbon::parse($request->date)->format('Y-m-d');
    // $from = Carbon::parse(trim($request->from_time))->format('g:i A');
    // $to   = Carbon::parse(trim($request->to_time))->format('g:i A');

    // $entry = Timesheet::where('emp_id', session('user_id'))
    //     ->where('create_dt', $date)
    //     ->where(function($q) use ($from, $to) {
    //         $q->where('from_time', '<', $to)
    //           ->where('to_time', '>', $from);
    //     })
    //     ->first();

    if ($entry) {
        return response()->json([
            'exists' => true,
            'entry' => [
                'project_id'  => $entry->project_id,
                'module_id'   => $entry->module_id,
                'task_id'     => $entry->task_id ?? 'other',
                'custom_task' => $entry->custom_task,
                'comments'    => $entry->comments,
                'id' => $entry->id,
                 'from_time'   =>  Carbon::parse($entry->from_time)->format('g:i A'),
                'to_time'     => Carbon::parse($entry->to_time)->format('g:i A')
            ]
        ]);
    }

    return response()->json(['exists' => false]);
}


public function edit_dtls($id){
    $entry = Timesheet::find($id);
return response()->json([
    'success' => true,
    'entry' => [
        'id'=> $id,
        'project_id'  => $entry->project_id,
        'module_id'   => $entry->module_id,
        'task_id'     => $entry->task_id ?? 'other',
        'custom_task' => $entry->custom_task,
        'custom_project' => $entry->custom_project,
        'custom_module' => $entry->custom_module,
        'comments'    => $entry->comments,
        'from_time'   =>  Carbon::parse($entry->from_time)->format('g:i A'),
        'to_time'     => Carbon::parse($entry->to_time)->format('g:i A'),
        'create_date'=> Carbon::parse($entry->create_dt)->format('d-m-Y'),
        'now' =>  Carbon::parse(now())->format('d-m-Y'),
    ]
]);
}
public function lastEntry(Request $request)
{
    $dateInput = $request->input('date');
    $date = Carbon::parse($dateInput)->format('Y-m-d');

    $lastEntry = Timesheet::where('emp_id', session('user_id'))
        ->whereDate('create_dt', $date)
        ->orderBy('id', 'desc')
        ->first();

    $slots = [
        ["from" => "9:00 AM",  "to" => "10:00 AM"],
        ["from" => "10:00 AM", "to" => "10:45 AM"],
        ["from" => "11:00 AM", "to" => "12:00 PM"],
        ["from" => "12:00 PM", "to" => "1:00 PM"],
        ["from" => "1:00 PM",  "to" => "1:30 PM"],
        ["from" => "2:00 PM",  "to" => "3:00 PM"],
        ["from" => "3:00 PM",  "to" => "4:00 PM"],
        ["from" => "4:15 PM",  "to" => "5:00 PM"],
        ["from" => "5:00 PM",  "to" => "6:00 PM"],
    ];

    if ($lastEntry) {
        $lastFrom = Carbon::parse($lastEntry->from_time)->format('g:i A');
        $lastTo   = Carbon::parse($lastEntry->to_time)->format('g:i A');
        $lastToTs = strtotime($lastTo);

        // Find the slot that contains last_to
        $slot = collect($slots)->first(function($s) use ($lastToTs) {
            $from = strtotime($s['from']);
            $to   = strtotime($s['to']);
            return $lastToTs >= $from && $lastToTs < $to;
        });

        if ($slot) {
            // Continue inside current slot
            $nextFrom = $lastTo;
            $nextTo   = $slot['to'];
        } else {
            // Fallback: next slot after last_to
            $slot = collect($slots)->first(function($s) use ($lastToTs) {
                return strtotime($s['from']) >= $lastToTs;
            });
            $nextFrom = $slot['from'] ?? null;
            $nextTo   = $slot['to'] ?? null;
        }
if( $lastFrom==Carbon::parse(session('entry_time_chkin'))->format('g:i A')){
        return response()->json([
            'success'   => true,
            'last_from' => $lastFrom,
            'last_to'   => $lastTo,
            'next_from' => $nextFrom,
            'next_to'   => $nextTo,
            'message'   => 'First Entry',
        ]);
} else {
        return response()->json([
            'success'   => true,
            'last_from' => $lastFrom,
            'last_to'   => $lastTo,
            'next_from' => $nextFrom,
            'next_to'   => $nextTo,
            'chk_in_time' => Carbon::parse(session('chkin_time'))->format('g:i A'),
            'sys_prob' => session('sys_problem'),
            'entry_time_chkin' => Carbon::parse(session('entry_time_chkin'))->format('g:i A'),
            'message'   => 'For All Entries',
        ]);
}
        return response()->json([
            'success'   => true,
            'last_from' => $lastFrom,
            'last_to'   => $lastTo,
            'next_from' => $nextFrom,
            'next_to'   => $nextTo,
            'chk_in_time' => Carbon::parse(session('chkin_time'))->format('g:i A'),
            'sys_prob' => session('sys_problem'),
            'entry_time_chkin' => Carbon::parse(session('entry_time_chkin'))->format('g:i A'),
            'message'   => 'Last entry found',
        ]);
    }

    // No entries yet → base on login time
    if ($date === now()->format('Y-m-d') && session('sys_problem') == 1) {
        $loginTime = session('entry_time_chkin');
    } else if ($date === now()->format('Y-m-d')) {
        $loginTime = session('chkin_time');
    } else {
        $attendance = Attendance::where('emp_id', session('user_id'))
            ->whereDate('chkoutDate', $date)
            ->first();
        $loginTime = $attendance ? $attendance->chkoutDate : null;
    }

    if (!$loginTime) {
        return response()->json([
            'success' => false,
            'message' => 'No login time found for this date',
        ], 404);
    }

    $loginTs = strtotime(Carbon::parse($loginTime)->format('g:i A'));

    // Find slot where login falls inside
    $slot = collect($slots)->first(function($s) use ($loginTs) {
        $from = strtotime($s['from']);
        $to   = strtotime($s['to']);
        return $loginTs >= $from && $loginTs < $to;
    });

    if (!$slot) {
        // Snap to next slot after login
        $slot = collect($slots)->first(function($s) use ($loginTs) {
            return strtotime($s['from']) >= $loginTs;
        });
    }

    if (!$slot) {
        return response()->json([
            'success'   => true,
            'last_from' => null,
            'last_to'   => null,
            'next_from' => null,
            'next_to'   => null,
            'message'   => 'No slots available after login time',
        ]);
    }

    return response()->json([
        'success'   => true,
        'last_from' => null,
        'last_to'   => null,
        'next_from' => Carbon::parse(session('chkin_time'))->format('g:i A'),
        'next_to'   => $slot['to'],
        'chk_in_time' => Carbon::parse(session('chkin_time'))->format('g:i A'),
        'sys_prob' => session('sys_problem'),
        'entry_time_chkin' => Carbon::parse(session('entry_time_chkin'))->format('g:i A'),
        'message'   => 'First slot based on login time',
    ]);
}
public function sendTimesheetReport(Request $request)
{
    $recipients = $request->input('recipients');

    if (empty($recipients)) {
        return response()->json(['success' => false, 'message' => 'No recipients selected'], 422);
    }

        $fileName = 'Timesheet_Report_'.session('user_id').'_' . now()->format('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/public/timesheet_reports/' . $fileName);

        Excel::store(new TimesheetExport($request), 'timesheet_reports/'.$fileName, 'public');
   

    // Dispatch queued job
    SendTimesheetReportJob::dispatch($recipients, $filePath, $fileName);

    return response()->json(['success' => true, 'message' => 'Report queued for sending']);
}
}
