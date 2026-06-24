<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Models\TicketType;
use App\Models\RaiseTicket;
use App\Models\ProblemType;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use App\Models\TicketAssignMembers;
use App\Models\ProjectStatus;
use App\Models\TeamMembers;
use App\Models\Teams;
use App\Models\TeamType;
use App\Jobs\SendTaskNotificationMail;
use Illuminate\Support\Facades\DB;
class TicketController extends Controller
{
    public function index(Request $request){

            // $ticket_id=22;
            // $notify_type = "ticket";
            // $subject = "Fortgrid -New Ticket Raised by ".session('user_name');
            // $message = "The Details are as follows:<br/><br/>";
            //     $message .="<br/>Ticket Name:".$request->input('ticket_name')."<br/>";
            //     $message .= "<br/><br/><a href='" . route('tickets.index') . "' target='_blank'>Click the Link For more details.</a><br/>";
            //     $base_content['message']=$message;
            //$this->SendNotificationTicket($ticket_id,$notify_type,$subject,$base_content);


        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
       $project_status= ProjectStatus::where('ticket_set_status',config('global.task_set_status'))->get();
            if((session('role_id')==config('global_roles.Project Manager')) && session('support_access')==true){
                $tickets = RaiseTicket::with('ticketType','problemType','ticketStatus','TicketOwner')
                                ->get();
            } else if(session('support_access')==true){
                $tickets = RaiseTicket::with('ticketType','problemType','ticketStatus','TicketOwner','AssignedTicketMembers')
                                        ->whereHas('AssignedTicketMembers', function ($query) {
                                            $query->where('assign_mem_id', session('user_id'));
                                        })
                                        ->get();
            } else {
                 $tickets = RaiseTicket::with('ticketType','problemType','ticketStatus')
                                ->where('ticket_raised_by',session('user_id'))
                                ->get();
            }
        if ($request->ajax()) {
        return DataTables::of($tickets)
            ->addIndexColumn()
            ->addColumn('ticket_type', function ($team) {
                        return $team->ticketType ? $team->ticketType->ticket_type : '-';
                    })
               ->addColumn('problem_type', function ($team) {
                        return $team->problemType ? $team->problemType->problem_type : '-';
                    })  
                    ->addColumn('ticket_status', function ($team) {
                        return $team->ticketStatus ? $team->ticketStatus->proj_status_name : '-';
                    }) 
                    ->addColumn('ticket_owner', function ($team) {
                        return $team->TicketOwner ? $team->TicketOwner->name : '-';
                    })        
                ->addColumn('action', function($row) {
                if($row->problem_type_active==1) {
                    $status=' <i class="fa fa-eye" title="active"></i>';
                }  else  {
                    $status='  <i class="fa fa-eye-slash"title="inactive"></i>';
                }
                    $action_button= '<button  data-id="'.$row->id.'" class="btn btn-success btn-sm editButton"><i class="fa fa-eye" title="active"></i></button>';
                      if((session('role_id')==config('global_roles.Project Manager')) && session('support_access')==true){   
                             $action_button.='&nbsp;|&nbsp;<button type="button" id="AssignTicket" class="btn btn-warning btn-sm" data-id="'.$row->id.'"  data-owner="'.$row->ticket_raised_by.'"  data-bs-placement="top" title="Assign Ticket"><i class="fa fa-tasks" aria-hidden="true"></i></button>';
                      } else {
                        if(session('support_access')==true){
                             $action_button.='&nbsp;|&nbsp;<button type="button" id="UpdateTicket" class="btn btn-primary btn-sm" data-id="'.$row->id.'"  data-bs-placement="top" title="Assign Ticket"><i class="fa fa-edit"></i></button>';
                        }
                      }

                    return $action_button;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        $LoadDatatables=true; 
        $ticket_type = TicketType::get();
        $problem_type=ProblemType::get();
        $support = User::where('emp_status', config('global.active_status'))->where('support_access',1)->select('id','name')->whereHas('roles', function ($query) {
                            $query->whereNotIn('roles.id',config('global.monitor_employees_act'));
                        }) ->where('id','!=',session('user_id'))->get();

        return view('my_profile.raise_ticket',compact('LoadDatatables','ticket_type','problem_type','support','project_status'));
    }
    public function store(Request $request){
        $data = $request->validate([
            'ticket_type_id' => 'required',
            'ticket_name' => 'required',
            'ticket_desc' => 'required'
        ]);
        $data=$request->all();
        $data['ticket_type_id']=$request->ticket_type_id;
        $data['problem_type_id']=$request->problem_type_id;
        $data['ticket_name']=$request->ticket_name;
        $data['ticket_desc']=$request->ticket_desc;
        $data['ticket_raised_by']=session('user_id');

        $ticket = RaiseTicket::create($data);
        $log_type="raise_ticket";
        $this->logProcessActivity('New Ticket Raised', $ticket,$log_type);    
            $ticket_id=$ticket->id;
            $notify_type = "ticket";
            $subject = "Fortgrid -New Ticket Raised by ".session('user_name');
            $message = "The Details are as follows:<br/><br/>";
                $message .="<br/>Ticket Name:".$request->input('ticket_name')."<br/>";
                $message .= "<br/><br/><a href='" . route('tickets.index') . "' target='_blank'>Click the Link For more details.</a><br/>";
                $base_content['message']=$message;
            $this->SendNotificationTicket($ticket_id,$notify_type,$subject,$base_content);
        return  response()->json(['success' => 'New Ticket details Added successfully!']);
    }

    public function update(Request $request){
        $data = $request->validate([
            'ticket_type_id' => 'required',
            'ticket_name' => 'required',
            'ticket_desc' => 'required'
        ]);
        $data=$request->all();
        $data['ticket_type_id']=$request->ticket_type_id;
        $data['problem_type_id']=$request->problem_type_id;
        $data['ticket_name']=$request->ticket_name;
        $data['ticket_desc']=$request->ticket_desc;
        $data['ticket_raised_by']=session('user_id');

        $category = RaiseTicket::find($request->id);
        $category->update($data);
        $log_type="raise_ticket";
        $this->logProcessActivity('Ticket Details Updated', $category,$log_type);    
        return  response()->json(['success' => 'Ticket details Updated successfully!']);
    }
    public function view_ticket(){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        return view('my_profile.raise_ticket');
    }
    public function edit($id)
    {
        $ticket_info = RaiseTicket::with('AssignedTicketMembers.user')->findOrFail($id);

        $members = $ticket_info->AssignedTicketMembers
            ->filter(function ($member) {
                return $member->user->id != session('user_id');
            })
            ->map(function ($member) {
                return [
                    'id' => $member->user->id,
                    'name' => $member->user->name,
                    'mem_comment'=> $member->reply_to,
                ];
            })
            ->values(); // optional: reindex the array
        $replyTo = optional($ticket_info->AssignedTicketMembers
                                 ->firstWhere('assign_mem_id', $ticket_info->ticket_solved_by))
                                 ->reply_to;

        return PermissionHelper::checkPermission('global.categories', $this->edit_perm)
            ? response()->json([
                'ticket' => $ticket_info,
                'assigned_members' => $members,
                'solved_by_reply_to' => $replyTo, 
            ])
            : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
    }
    public function ticket_types(){
        return view('tickets.ticket_types'); 
    }
    public function assign_ticket(Request $request, $id){
            $request->validate([
                'assign_mem_id' => 'required|array',
                'assign_comments' => 'nullable|string',
                'ownerId' => 'required|integer',
            ]);
        $all_emp_ids=array();
            $ticket_id = $request->hid_id;
            $owner_id = $request->ownerId;
            $all_emp_ids[$owner_id] = 0;
            $assign_comments = $request->assign_comments;
            $assign_mem_ids = $request->assign_mem_id;
            TicketAssignMembers:: where('ticket_id', $ticket_id)->delete();
                // Store each assigned member
                foreach ($assign_mem_ids as $member_id) {
                    $assign_info=TicketAssignMembers::create([
                        'ticket_id' => $ticket_id,
                        'owner_id' => $owner_id,
                        'assign_mem_id' => $member_id,
                        'assign_comments' => $assign_comments,
                    ]);
                     $log_type="ticket_assign";
                 $this->logProcessActivity('New Ticket Assigned', $assign_info,$log_type);  
                    $all_emp_ids[$member_id] = 0;
                }
                $ticket_info = RaiseTicket::find($ticket_id);
                $ticket_info->update(['ticket_status' => config('global.in_progress')]);

                $ticket_id=$ticket_id;
                 $log_type="raise_ticket";
                 $this->logProcessActivity('New Ticket Assigned', $ticket_info,$log_type);  

                $notify_type = "ticket_assign";
                $subject = "Fortgrid - New Ticket Assigned by ".session('user_name');
                $message = "The Details are as follows:<br/><br/>";
                $message .="<br/>Ticket Name:".$ticket_info->ticket_name."<br/>";
                $message .= "<br/><br/><a href='" . route('tickets.index') . "' target='_blank'>Click the Link For more details.</a><br/>";
                $base_content['message']=$message;
                $base_content['subject']=$subject;


                $subject = "Fortgrid -Your Ticket Status is Changed and Assigned to support team by ".session('user_name');
                $message = "The Details are as follows:<br/><br/>";
                $message .="<br/>Ticket Name:".$ticket_info->ticket_name."<br/>";
                $message .= "<br/><br/>Keep Checking on the <a href='" . route('tickets.index') . "' target='_blank'>Link</a> to more details.<br/>";
                $base_content['owner_message']['message']=$message;
                $base_content['owner_subject']=$subject ;
                $this->SendNotificationTicket($ticket_id,$notify_type,$subject,$base_content,$all_emp_ids);

                return response()->json(['success' => 'Ticket assigned successfully.']);
    }
   
    public function assigned_members($id){
        $mem_details=TicketAssignMembers::where('ticket_id', $id)->get();
        return response()->json($mem_details);
    }
    public function ticket_ind_update(Request $request){
        $member = TicketAssignMembers::where('ticket_id', $request->ticket_id)
                    ->where('assign_mem_id', session('user_id'))
                    ->first();

                if ($member) {
                 $rs=   $member->update(['reply_to' => $request->reply_to]);
                    // Now $member contains the updated model with all details
                }

       // $rs= TicketAssignMembers::where('ticket_id',$request->ticket_id)->where('assign_mem_id',session('user_id'))->update(['reply_to' => $request->reply_to]);
            $log_type="ticket_status";
            $this->logProcessActivity('Ticket Status Changed', $rs,$log_type); 
            if(!empty($request->ticket_status)){
                $solved_by=($request->ticket_status==config('global.completed_status'))? session('user_id') :0;
                $ts= RaiseTicket::find($request->ticket_id);  
             if (!$ts) {
                    return response()->json([
                        'error' => 'Ticket not found for ID ' . $request->ticket_id
                    ], 404);
                } else {
                        // Safe to use $ts now
                        $ts->update([
                            'ticket_solved_by' => $solved_by,
                            'ticket_status'    => $request->ticket_status
                        ]);

                        // $ts->update(['ticket_solved_by'=> $solved_by,'ticket_status'=> $request->ticket_status]);
                            $log_type="ticket_status";
                            $this->logProcessActivity('Ticket Status Changed', $rs,$log_type);
                        
                    $all_emp_ids=array();
                    if($request->ticket_status==config('global.completed_status')){
                        $all_emp_ids[$ts->ticket_raised_by] = 0;
                    } 
                        $notify_type = "ticket";
                        $subject = "Fortgrid - Ticket Status changed by ".session('user_name');
                        $message = "The Details are as follows:<br/><br/>";
                        $message .="<br/>Ticket Name:".$ts->ticket_name."<br/>";
                        $message .= "<br/><br/><a href='" . route('tickets.index') . "' target='_blank'>Click the Link For more details.</a><br/>";
                        $base_content['message']=$message;
                        $this->SendNotificationTicket($request->ticket_id,$notify_type,$subject,$base_content,$all_emp_ids);
                }
        } else{ 
            $member_other = TicketAssignMembers::where('ticket_id', $request->ticket_id)
                ->where('assign_mem_id','!=', session('user_id'))
                ->pluck('assign_mem_id');

            logger()->info('Sender found:', $member_other->toArray());

            $all_emp_ids = [];
            foreach ($member_other as $emp) {
                $all_emp_ids[$emp] = 0;
            }
            $notify_type = "ticket";
            $subject = "Fortgrid - ".session('user_name')." Added comments for the ticket -".$request->ticket_name;
            $message = "The Details are as follows:<br/><br/>";
            $message .="<br/>Ticket Name:".$request->ticket_name."<br/>";
            $message .= "<br/><br/><a href='" . route('tickets.index') . "' target='_blank'>Click the Link For more details.</a><br/>";
            $base_content['message']=$message;
            $this->SendNotificationTicket($request->ticket_id,$notify_type,$subject,$base_content,$all_emp_ids);
        }
                return response()->json(['message' => 'Ticket Status Updated successfully!.'],200);
    }
public function SendNotificationTicket($ticket_id,$notify_type,$subject,$base_content,$norma_emps=[])
{
    $all_emp_ids = [];
    if (is_array($norma_emps)) {
        foreach($norma_emps as $ind_emps => $val){
            $all_emp_ids[$ind_emps] = $val;
        }
    }
     $ticket = RaiseTicket::findOrFail($ticket_id);
     $owner_id=$ticket->ticket_raised_by;
    
    if($notify_type=='ticket'){
        $actorUserId = session('user_id');
        $teams = TeamMembers::where('emp_id', $actorUserId)->pluck('team_id')->toArray();
        $team_type = Teams::where('id', $teams)->pluck('team_type')->toArray();
        $team_type[]=3;
        $pmIds = TeamType::whereIn('id', $team_type)
                            ->pluck('pm_id')
                            ->unique()
                            ->filter()
                            ->toArray();
        foreach ($pmIds as $pmId) {
            if (!isset($all_emp_ids[$pmId])) {
                $all_emp_ids[$pmId] = 1; // or 1 if you want them as reporting
            }
        }
    }

    $reportingView = $all_emp_ids;
   
    $senderId = session('user_id') ?? config('global.superadmin_id');
    $sender = User::find($senderId);
    
    if (is_null($sender)) {
        logger()->error("Sender not found. ID: $senderId");
        $senderMeta = config('global.admin');
    } else {
        logger()->info('Sender found:', $sender->toArray());
        $senderMeta = [
            'id' => $senderId,
            'name' => session('user_name') ?? $sender->name,
            'role' => session('role_name') ?? optional($sender->roles)->pluck('role_name')->first(),
        ];
    }
    foreach ($all_emp_ids as $empId => $roleFlag) {
        $user = User::find($empId);
        if (!$user) continue;
        $isControl = $roleFlag == 1;
        $isSelfPM =!emptY($pmIds) &&  in_array($empId, $pmIds) && session('user_id') == $empId;
       // logger()->info('Dispatching for', ['emp_id' => $empId, 'isSelfPM' => $isSelfPM]);
         if($notify_type=='ticket'){
            $message = $this->storeStatusNotification($user, $ticket, $reportingView, $senderMeta, $subject, $base_content, $notify_type, $isControl);
         } else {
            if($empId==$owner_id){
               $message = $this->storeStatusNotification($user, $ticket, $reportingView, $senderMeta, $base_content['owner_subject'], $base_content['owner_message'], $notify_type, $isControl); 
               $subject=$base_content['owner_subject'];
                //logger()->info('Dispatching for Owner', ['emp_id' => $empId, 'Owner' =>'Yes']);
            } else {
                $message = $this->storeStatusNotification($user, $ticket, $reportingView, $senderMeta, $base_content['subject'], $base_content, $notify_type, $isControl); 
              //  logger()->info('Dispatching for other employees', ['emp_id' => $empId, 'Owner' =>'NO']);
                $subject=$base_content['subject'];
            }
         }
         logger()->info('Sender Info:', $senderMeta);
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
public function update_status($id){
 $ticket_info = RaiseTicket::with('AssignedTicketMembers')->findOrFail($id);
 $members = $ticket_info->AssignedTicketMembers
            ->filter(function ($member) {
                return $member->user->id == session('user_id');
            })
            ->map(function ($member) {
                return [
                    'id' => $member->user->id,
                    'name' => $member->user->name,
                    'mem_comment'=> $member->reply_to,
                ];
            })
            ->values(); 
  return response()->json([
                'ticket' => $ticket_info,
                'assign_members' => $members,
            ]);
}
}
