<?php

namespace App\Http\Controllers;
use App\Models\TeamMembers;
use App\Models\TeamType;
use App\Models\Teams;
use App\Models\User;
use App\Models\TaskAssignEmp;
use App\Models\Timesheet;
use Illuminate\Support\Carbon;
use App\Models\PMTasksAssign;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendTaskNotificationMail;
use App\Helpers\PermissionHelper;
use App\Helpers\ActivityHelper;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    protected $view_perm,$add_perm,$edit_perm,$del_perm,$load_permission_page,$load_roles_page,$load_users_page,$permission_array;

    public function __construct()
    {
        // Initialize the property from the config
        /**Refer AppServiceProvider and config/global.php */
        $this->view_perm = config('global_permissions.View');
        $this->add_perm = config('global_permissions.Add');
        $this->edit_perm = config('global_permissions.Edit');
        $this->del_perm = config('global_permissions.Delete');
        
        $this->load_permission_page = config('global.load_permissions');
        $this->load_roles_page = config('global.load_roles');
        $this->load_users_page = config('global.load_users');
        $this->permission_array=[
            'add'=>$this->add_perm,
            'edit'=>$this->edit_perm,
            'view'=>$this->view_perm,
            'delete' =>$this->del_perm
 ];
    }
   public function get_uri_segment($segment_no) {
        $url = $_SERVER['REQUEST_URI']; // Gets the path after the domain
        $segments = explode('/', trim($url, '/')); // Remove leading/trailing slashes and split
        return isset($segments[$segment_no]) ? $segments[$segment_no] : null;
    }
    public function hasPermission($action)
    {
        return PermissionHelper::checkPermission('global.categories', $this->$action)
            ? true
            : redirect()->route('dashboard')->withMessage('Not Authorized');
    }
      public function logProcessActivity($message, $category,$type)
    {
        ActivityHelper::logActivity($message, $type, $category, [
            'request' => request()->all()
        ]);
    }
    public function AttendanceNotification($notify_type,$subject,$base_content,$emp_id='',$selfsent='',$leave_status='')
    {
        $emp_id=!empty($emp_id)?$emp_id : session('user_id');
     $taskIds = PMTasksAssign::where('employee_id', $emp_id)
                                ->whereHas('task', function($q) {
                                    $q->where('task_status', '!=', config('global.completed_status'));
                                })
                                ->pluck('task_id')
                                ->toArray();
    // $all_emp_ids = TeamMembers::whereIn('team_id', $teamId)
    //                 ->where('ctrl_status', config('global.ctrl_status'))
    //                 ->pluck('ctrl_status','emp_id')->toArray();
                                
    // $teamTypeIds = Teams::whereIn('id', $teamId)->pluck('team_type')->toArray();
    // $pmIds = TeamType::whereIn('id', $teamTypeIds)
    //              ->pluck('pm_id')
    //              ->toArray();
    if(session('role_id')!=config('global.first_level_role')){
     $pmIds=DB::table('roles_user')
            ->whereIn('roles_id',config('global.task_approve_roles'))
            ->where('roles_id','!=',session('role_id'))
            ->pluck('user_id')
            ->toArray();
        foreach ($pmIds as $pmId) {
                if (!isset($all_emp_ids[$pmId])) {
                    $all_emp_ids[$pmId] = config('global.ctrl_status'); //  1 if you want them as reporting
                }
            }
    }
        $hrIds=DB::table('roles_user')
            ->where('roles_id',config('global_roles.HR'))
            ->pluck('user_id')
            ->toArray();
        foreach ($hrIds as $hrId) {
                if (!isset($all_emp_ids[$hrId])) {
                    $all_emp_ids[$hrId] = 1; // or 1 if you want them as reporting
                }
            }
    if(($notify_type== "leave_approval" || $notify_type== "permission_approval") && $leave_status==1){

           $additional_emp_ids = PMTasksAssign::whereIn('task_id', $taskIds)
                    ->where('employee_id','!=', $emp_id)
                    ->pluck('employee_id')->toArray(); 
            foreach ($additional_emp_ids as $extraId) {
                if (!isset($all_emp_ids[$extraId])) {
                    $all_emp_ids[$extraId] = 0; // mark them with 0
                }
            }
    }
          if(!empty($emp_id)){
            $all_emp_ids[$emp_id] =0;
           }
        $reportingView = collect($all_emp_ids)
            ->map(fn($id) => $members[$id] ?? null)
            ->filter()
            ->values()
            ->toArray();     
   //logger()->info('Dispatching for test123', ['reportingView' => $all_emp_ids]);
 $senderId = !empty(session('user_id')) ? session('user_id') : config('global.superadmin_id');
 $sender = User::find($senderId);
  $senderMeta = [
                'id' => $senderId,
                'name' => $sender->name,
                'role' => $sender->roles->pluck('role_name')->first(),
                ];

foreach ($all_emp_ids as $empId => $roleFlag) {
    $base_link='';
    if($empId==$emp_id){
        $base_link="<br/><br/><br/><br/><a href='" . route('attendance.applied_leaves') . "' target='_blank'>Click the Link For more details.</a><br/>";
    } else {
      $base_link='';  
    }
    //logger()->info('Dispatching for loop', ['emp_id' => $empId, 'flag' => $roleFlag,'orig_emp_id' => $emp_id]);
            $user = User::find($empId);
            if (!$user || ($empId==$emp_id && ($selfsent==false || empty($selfsent)))) continue;
            $isControl = $roleFlag == 1;
            $isSelfPM = in_array($empId, $pmIds) && $emp_id == $empId;
            logger()->info('Dispatching for', ['emp_id' => $empId, 'isSelfPM' => $isSelfPM]);
            $sender_name= ($senderMeta['id']!=$user->id) ? $senderMeta['name'] : config('global.admin.name');
                $message=$this->storeNotification($user, $senderMeta, $subject, $base_content.$base_link,$notify_type,$isControl);
                  dispatch(new SendTaskNotificationMail(
                            $user,         
                            $subject,      
                            $message,
                            $sender_name     
                        ));
                       // sleep(2);
        }
    return response()->json(['message' => 'notifications dispatched.']);
}
public function storeNotification($receiver,$senderMeta, $subject,$base_content,$notifyType,$excludeSelf = false){
    if($senderMeta['id']!=$receiver->id){
    DB::table('notification')->insert([
            'notify_type'   => $notifyType,
            'receiver_id'   => $receiver->id,
            'sender_id'     => $senderMeta['id'],
            'sender_name'   => $senderMeta['name'] . ' - ' . $senderMeta['role'],
            'receiver_name' => $receiver->name,
            'is_read'       => config('global.notify_unread'),
            'subject'       => $subject,
            'message'       => $base_content,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
        return $base_content;
    }
public function SendNotificationIndividually($all_emp_ids,$subject,$base_content,$notify_type){
                $senderId = !empty(session('user_id')) ? session('user_id') : config('global.superadmin_id');
                $sender = User::find($senderId);
                $senderMeta = [
                                'id' => $senderId,
                                'name' => $sender->name,
                                'role' => $sender->roles->pluck('role_name')->first(),
                                ];
                foreach ($all_emp_ids as $empId) {
                // logger()->info('Dispatching for', ['emp_id' => $empId, 'flag' => $roleFlag]);
                            $user = User::find($empId);
                            if (!$user) continue;
                            $sender_name= ($senderMeta['id']!=$user->id) ? $senderMeta['name'] : config('global.admin.name');
                                // $base_content.= "<br/><br/><a href='" . route('attendance.applied_leaves') . "' target='_blank'>Click the Link For more details.</a><br/>";
                            $message=$this->storeNotification($user, $senderMeta, $subject,$base_content,$notify_type);
                                dispatch(new SendTaskNotificationMail(
                                            $user,         
                                            $subject,      
                                            $message,
                                            $sender_name     
                                        ));
                                    // sleep(2);
                }
        return response()->json(['message' => 'notifications dispatched.']);
}
public function storeStatusNotification($receiver, $task,$reportingView, $senderMeta, $subject,$base_content,$notifyType,$excludeSelf = false)
{
    DB::table('notification')->insert([
        'notify_type'   => $notifyType,
        'receiver_id'   => $receiver->id,
        'sender_id'     => $senderMeta['id'],
        'sender_name'   => $senderMeta['name'] . ' - ' . $senderMeta['role'],
        'receiver_name' => $receiver->name,
        'is_read'       => config('global.notify_unread'),
        'subject'       => $subject,
        'message'       => $base_content['message'],
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return $base_content['message']; // useful for passing to the job for email if needed
}
public function storeStatusNotificationDB($notify_type,$sender,$receiver,$subject,$message){
    $res= DB::table('notification')->insert([
        'notify_type'   => $notify_type,
        'receiver_id'   => $receiver['id'],
        'sender_id'     => $sender['id'],
        'sender_name'   => $sender['name'] . ' - ' . $sender['role'],
        'receiver_name' => $receiver['name'],
        'is_read'       => config('global.notify_unread'),
        'subject'       => $subject,
        'message'       => $message,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return $message;
}
public function AlertNotifications($notify_type,$sender,$receiver,$subject,$message){
    foreach ($receiver as $indiv_receive){
       $message= $this->storeStatusNotificationDB($notify_type,$sender,$indiv_receive,$subject,$message);  
       $user = User::find($indiv_receive['id']); 
         dispatch(new SendTaskNotificationMail(
                                            $user,         
                                            $subject,      
                                            $message,
                                            $sender['name']     
                                        ));                                 
    }
    return response()->json(['message' => 'notifications dispatched.']);
    
}
public function storeSystemProblemEntries() {
    if (session('sys_problem') == 1) {
        $empId = auth()->id();
        $checkinTime = Carbon::parse(session('chkin_time'));       //  actual login time
        $lastProblemTime = Carbon::parse(session('last_prob_time')); //  problem end time

        $date = now()->format('Y-m-d');

        // Loop through slots
        $times = [
            "9:00am - 10:00am", "10:00am - 10:45am", "11:00am - 12:00pm",
            "12:00pm - 1:00pm", "1:30pm - 2:00pm", "2:00pm - 3:00pm",
            "3:00pm - 4:00pm", "4:15pm - 5:00pm", "5:00pm - 6:00pm"
        ];

        foreach ($times as $slot) {
            [$start, $end] = explode('-', $slot);
            $slotStart = Carbon::parse($date . ' ' . trim($start));
            $slotEnd   = Carbon::parse($date . ' ' . trim($end));

            // Skip slots that end before login
            if ($slotEnd->lte($checkinTime)) {
                continue;
            }

            //  Skip slots that start after problem ended
            if ($slotStart->gte($lastProblemTime)) {
                break;
            }

            // If login falls inside this slot, adjust start
            if ($checkinTime->between($slotStart, $slotEnd)) {
                $slotStart = $checkinTime->copy();
            }

            //If problem ends inside this slot, adjust end
            if ($lastProblemTime->between($slotStart, $slotEnd)) {
                $slotEnd = $lastProblemTime->copy();
            }

            $durationMinutes = $slotStart->diffInRealMinutes($slotEnd);
            //Log::info("Minutes calculation from".$slotStart->format('g:i A').' to'.  $slotEnd->format('g:i A').' is '. $durationMinutes);
            if ($durationMinutes > 0) {
                // Log::info("Minutes calculation from".$slotStart->format('g:i A').' to'.  $slotEnd->format('g:i A').' is '. $durationMinutes);
                Timesheet::updateOrCreate(
                    [
                        'emp_id'    => $empId,
                        'create_dt' => $date,
                        'from_time' => $slotStart->format('g:i A'),
                        'to_time'   => $slotEnd->format('g:i A'),
                    ],
                    [
                        'project_id'     => null,
                        'module_id'      => null,
                        'day'            => Carbon::parse($date)->dayName,
                        'comments'       => session('reason_chkin'),
                        'task_id'        => null,
                        'custom_task'    => "System Problem",
                        'custom_project' => "System Problem",
                        'custom_module'  => "System Problem",
                        'duration'       => floor($durationMinutes),
                    ]
                );
            }
        }

        // Update session with last problem boundary
        session(['entry_time_chkin' => $lastProblemTime->format('g:i A')]);
    }
}
}