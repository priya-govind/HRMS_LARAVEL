<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Helpers\ActivityHelper;
use App\Models\TeamType;
use App\Models\User;
use App\Events\NotifyInfo;

use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class TeamTypeController extends Controller
{
    public function team_types(Request $request){
      $LoadDatatables=true;
     $pms = User::whereHas('roles', function($query) {
                      $query->where('roles.id', 3);
                  })->pluck('name','id')->toArray();
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->view_perm);
        if(!$cat_permission){ 
          return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
        $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);
          if ($request->ajax()) {
            $team_types = TeamType::get();
            return DataTables::of($team_types)
                ->addIndexColumn()
                   ->addColumn('action', function($row)  use ($edit_permit, $delete_permit) {
                     $editButton = $edit_permit
                            ? '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>' 
                            : '';
                    $deleteButton = $delete_permit 
                                ? '&nbsp;|&nbsp;
                      <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>' 
                                : '';
                    return $editButton.$deleteButton;
                })
                ->rawColumns(['action'])
                ->make(true);
          }
          return view('teams.team_types',compact('pms','LoadDatatables'));
    }
    public function edit_team_type($id){
            $team_types= TeamType::find($id); 
            return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($team_types) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    }
    public function store_team_type(Request $request){
          $validated = $request->validate([
              'team_typ_name' => 'required|unique:team_type' ,
              'pm_id' => 'required',
              'team_color' =>'required',
          ]);
          
         $data = $request->except('_token');
          // Mass assigment
         $team_types= TeamType::create($data);
         $info['notify_type']="team_type";
         $info['team_types']=$team_types;
         if ($team_types) {
                event(new NotifyInfo($info));
            }
          $log_name='Team type';
          ActivityHelper::logActivity('Create New Team Type',$log_name, $team_types, [
            'request' => request()->all()
        ]);
          return  response()->json(['message' => 'Team Type Added successfully!']);
          // return redirect()->route('permissions')
          //         ->withSuccess('New Permission created successfully.');
    }
    public function update_team_type(Request $request){
        $team_types= TeamType::find($request->id);
        $request->validate([
           'team_typ_name' => 'required|unique:team_type,team_typ_name,'.$request->id,
           'pm_id' => 'required',
           'team_color' =>'required',
        ]);
        $log_name='Team type';
          ActivityHelper::logActivity('Edit Team Type',$log_name, $team_types, [
            'request' => request()->all()
        ]);
        $data = $request->all();
        
        $team_types->update($data);
           $info['notify_type']="team_type";
         $info['team_types']=$team_types;
         if ($team_types) {
                event(new NotifyInfo($info));
            }
        return  response()->json(['message' => 'Team Type Updated successfully!']);
    }
    public function destroy_team_type($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
       if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
       }else{
        $user = Auth::user();
        $team_types= TeamType::find($id);
      if ($team_types) {
      $log_name='Team type';
           ActivityHelper::logActivity('Team Type Deleted',$log_name, $team_types, [
                      'request' => request()->all()
                  ]);
          $team_types->delete();
      }
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }

}
