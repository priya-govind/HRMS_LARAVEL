<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Models\Teams;
use App\Models\TeamType;
use App\Models\ProjectType;
use App\Models\User;
use App\Models\TeamMembers;
use App\Helpers\ActivityHelper;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Events\NotifyInfo;


class TeamController extends Controller
{

    public function list_teams(Request $request){
        $LoadDatatables=true; 
                 /** PM*/          
// if (in_array(session('role_id'), config('global.first_level_role'))) {
//     // $teams = Teams::with('teamType','projType')
//     //                   ->whereHas('teamType', function ($query) {
//     //                         $query->where('pm_id', session('user_id'));
//     //                     })
//     //                 ->select('id', 'team_name', 'team_type','proj_type')->get();
// } else 
if (in_array(session('role_id'), config('global.task_monitor_roles'))) {
    $teams = Teams::with('teamType','projType')
                      ->whereHas('teamMembers', function ($query) {
                            $query->where('ctrl_status', 1)
                                ->where('emp_id', session('user_id'));
                        })
                    ->select('id', 'team_name', 'team_type','proj_type')->get();
    } else if(in_array(session('role_id'), config('global.monitor_employees_act'))) {
        $teams = Teams::with('teamType','projType')->select('id', 'team_name', 'team_type','proj_type')->orderBy('id', 'desc')->get();
    }
    else  {
    $teams = Teams::with('teamType','projType')->whereIn('id',session('team_id'))->select('id', 'team_name', 'team_type','proj_type')->orderBy('id', 'desc')->get();
    }
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
         $team_types=TeamType::get();
         $proj_types=ProjectType::get();   

         $task_monitor_roles= config('global.task_monitor_roles');             
          $control_team=User::where('emp_status',config('global.active_status'))
                        ->where('id','!=', config('global.superadmin_id'))
                        ->whereHas('roles', function ($query) use ($task_monitor_roles) {
                            $query->whereIn('roles_id', $task_monitor_roles);
                        })
                       ->get(); 
            $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
            $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);               
        if ($request->ajax()) {
            //$teams = Teams::with('teamType','projType')->select('id', 'team_name', 'team_type','proj_type')->get();
            return DataTables::of($teams)
                    ->addIndexColumn()
                     ->addColumn('proj_typ_name', function ($team) {
                        return $team->projType ? $team->projType->proj_typ_name : 'N/A';
                    })
                    ->addColumn('type_name', function ($team) {
                        return $team->teamType ? $team->teamType->team_typ_name : 'N/A';
                    })
                   ->addColumn('action', function($row)  use ($edit_permit, $delete_permit) {
                    $viewButton='<button data-id="'.$row->id.'" class="btn btn-success btn-sm TeamsButton" title="Team Members"><i class="fa fa-users" aria-hidden="true"></i></button>';
                     $editButton = $edit_permit
                            ? '&nbsp;|&nbsp;<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>' 
                            : '';
                    $deleteButton = $delete_permit
                                ? '&nbsp;|&nbsp;<button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>' 
                                : '';
                    

                    return  $viewButton.$editButton.$deleteButton;
                })
                ->rawColumns(['action'])
                ->make(true);
          }
        return view('teams.view',compact('team_types','proj_types','control_team','LoadDatatables'));
    }
    public function edit_team($id){
        if (!PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $teams= Teams::find($id); 
        $selectedMembers = TeamMembers::where('team_id', $id)->pluck('emp_id')->toArray();
        //$ctrl_members = TeamMembers::where('team_id', $id)->where('ctrl_status',config('global.ctrl_status'))->pluck('emp_id')->toArray();
         $ctrl_members = TeamMembers::with('user') 
                                ->whereHas('user', function ($query) use ($teams) {
                                    $query->whereIn('users.team_type', [$teams->team_type]); 
                                })
                                ->where('team_id', $id) 
                                ->where('ctrl_status', config('global.ctrl_status')) 
                                ->pluck('emp_id') 
                                ->toArray();
    return response()->json([
        'id' => $teams->id,
        'team_name' => $teams->team_name,
        'team_type' => $teams->team_type,
        'proj_type' => $teams->proj_type,
        'selected_members' => $selectedMembers,
        'ctrl_members' => $ctrl_members
    ]);
}

public function store_team(Request $request){
      $validated = $request->validate([
          'team_name' => 'required|unique:teams',
          'team_type' => 'required',
          'proj_type' =>'required', 
      ]);
      
     $data = $request->except('_token');
      // Mass assigment
     $teams= Teams::create($data);
      $log_name='Teams';
      ActivityHelper::logActivity('New Team Created',$log_name, $teams, [
        'request' => request()->all()
    ]);

foreach($request->emp_id as $empp){
    $team_members=[
        'team_id' => $teams->id, // Example field for document ID, adjust if needed
        'emp_id' => $empp,
        'proj_type'=>$request->proj_type

    ];
   $team_info= TeamMembers::create($team_members);
     $user = User::find($empp, ['name', 'email','id']);
         $info['notify_type']="teams";
         $info['user']=$user;
         $info['team_name']=$request->team_name;
         if ($user) {
                event(new NotifyInfo($info));
            }
}
    foreach($request->ctrl_id as $empp){
        $team_members=[
            'team_id' => $teams->id, // Example field for document ID, adjust if needed
            'emp_id' => $empp,
            'proj_type'=>$request->proj_type,
            'ctrl_status'=>config('global.ctrl_status')
        ];
        TeamMembers::create($team_members);
        $user = User::find($empp, ['name', 'email','id']);
         $info['notify_type']="teams";
         $info['user']=$user;
         $info['team_name']=$request->team_name;
         if ($user) {
                event(new NotifyInfo($info));
            }
    }
    /**when new team added reporting member session update */
    if(in_array(session('role_id'),config('global.task_approve_roles'))){
        $teams = Teams::whereIn('team_type', session('team_type'))->pluck('id')->toArray();
          session(['team_id' => (array) $teams]);
    }

                        
     return  response()->json(['message' => 'Team Added successfully!']);

}
public function update_team(Request $request){

    $teams= Teams::find($request->id);
    $request->validate([
       'team_name' => 'required|unique:teams,team_name,'.$request->id,
       'team_type' => 'required', 
       'proj_type' =>'required',
    ]);
    $log_name='Teams';
      ActivityHelper::logActivity('Edit Team',$log_name, $teams, [
        'request' => request()->all()
    ]);
    $data = $request->all();
    
    $teams->update($data);
    TeamMembers::where('team_id',$request->id)->delete();
    foreach($request->emp_id as $empp){
        $team_members=[
            'team_id' => $request->id, // Example field for document ID, adjust if needed
            'emp_id' => $empp,
            'proj_type'=>$request->proj_type
        ];
       TeamMembers::create($team_members);
         $user = User::find($empp, ['name', 'email','id']);
         $info['notify_type']="teams";
         $info['user']=$user;
         $info['team_name']=$request->team_name;
         if ($user) {
                event(new NotifyInfo($info));
            }
    }

    foreach($request->ctrl_id as $empp){
        $team_members=[
            'team_id' => $request->id, // Example field for document ID, adjust if needed
            'emp_id' => $empp,
            'proj_type'=>$request->proj_type,
            'ctrl_status'=>config('global.ctrl_status')
        ];
        TeamMembers::create($team_members);
         $user = User::find($empp, ['name', 'email','id']);
         $info['notify_type']="teams";
         $info['user']=$user;
         $info['team_name']=$request->team_name;
         if ($user) {
                event(new NotifyInfo($info));
            }
    }

    return  response()->json(['message' => 'Team Updated successfully!']);
}
public function destroy_team($id){
    $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
   if(!$cat_permission){ 
    return  response()->json(['message' => 'Not Authorized to see this page.'],200);
   }else{
    $user = Auth::user();
    $teams= Teams::find($id);
  if ($teams) {
  $log_name='Teams';
       ActivityHelper::logActivity('Team Deleted',$log_name, $teams, [
                  'request' => request()->all()
              ]);
      $teams->delete();
  }
    return response()->json(['message' => 'Record Deleted successfully!'],200);
    }
}
public function getTeams(Request $request){
    $teams = Teams::whereIn('team_type', $request->team_types)->where('proj_type',$request->proj_type)->pluck('team_name', 'id'); // Adjust based on your database structure
   
    return response()->json($teams);
}
public function getTeamMembers(Request $request){
    $teamIds = $request->team_ids; // Expecting an array of selected team IDs
   
    $members =TeamMembers::with('user','team', 'user.roles')
            ->where('ctrl_status','!=',config('global.ctrl_status'))
            ->whereIn('team_id',$teamIds)
            ->whereHas('user', function ($query) {
                $query->where('emp_status', config('global.active_status'));
            })
             ->whereHas('user.roles', function ($query) {
                    $query->whereNotIn('roles_id', config('global.task_approve_roles')); // Adjust condition as needed
                })

            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->user->id,
                    'team_id'=>$member->team_id,
                    'name' => $member->user->name,
                    'team_name' => $member->team->team_name,
                ];
            });
    return response()->json($members);
}
public function GetTeamMemebersAssign(Request $request){
     $task_approve_roles= config('global.task_approve_roles');
           $employees = User::where('emp_status', config('global.active_status'))
                            ->where('team_type',$request->team_types)
                           ->whereHas('roles', function ($query) use ($task_approve_roles) {
                                 $query->whereNotIn('roles_id', $task_approve_roles);
                             })
                        ->get()
                         ->map(function ($member) {
                                return [
                                    'id' => $member->id,
                                    'name' => $member->name,
                                ];
                            });
    return response()->json($employees);

}
public function GetCtrlMembersAssign(Request $request){
     $task_approve_roles= config('global.task_monitor_roles');
           $employees = User::where('emp_status', config('global.active_status'))
                            ->where('team_type',$request->team_types)
                           ->whereHas('roles', function ($query) use ($task_approve_roles) {
                                 $query->whereIn('roles_id', $task_approve_roles);
                             })
                        ->get()
                         ->map(function ($member) {
                                return [
                                    'id' => $member->id,
                                    'name' => $member->name,
                                ];
                            });
    return response()->json($employees);
}
public function list_team_members($id)
{
    $team = Teams::findOrFail($id);
    $members = $team->members_info_role;
       return [
        'reporting_members'=> $members->where('ctrl_status', 1)->values(),
         'team_members'     => $members->where('ctrl_status', 0)->values(),
    ];
}

}
