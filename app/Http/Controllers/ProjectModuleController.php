<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ActivityHelper;
use App\Helpers\PermissionHelper;
use App\Models\Projects;
use App\Models\User;
use App\Models\ProjectModule;
use App\Models\ProjectModuleAssign;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class ProjectModuleController extends Controller
{
    public function index(Request $request){
      if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
          // Redirect if permission is denied
          return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
      }  
      $projects=  Projects::get();
      $employees =User::with('roles')->where('emp_status',config('global.active_status'))->whereHas('roles', function ($query) {
                $query->whereNotIn('roles.id', config('global.restriction_free_roles'));
            })->get();
       $LoadDatatables=true;  
       $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
       $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);  
      if ($request->ajax()) {
        $projects= ProjectModule::with('projects');
        $projects->orderBy('id', 'desc')->get();

        return DataTables::of($projects)
            ->addIndexColumn()
              ->addColumn('proj_name', function($row){
                  return optional($row->projects)->proj_name ?? 'Not Present';
              })
           ->addColumn('action', function($row) use ($edit_permit, $delete_permit) {
               $editButton = $edit_permit
                            ? '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>' 
                            : '';
               $deleteButton = $delete_permit
                                ? '&nbsp;|&nbsp;<button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>' 
                                : '';
                $viewButton='&nbsp;|&nbsp;<button data-id="'.$row->id.'" class="btn btn-primary btn-sm ModulesButton"><i class="fa fa-users"></i></button>' ;

                return $editButton . $deleteButton.$viewButton;
            })
            ->rawColumns(['action'])
            ->make(true);
      }
      return view('tasks.manage_projects_modules', compact('projects','LoadDatatables','employees'));
    }
    public function store(Request $request){
    try {
        // Validation
        $validated = $request->validate([
            'module_name' => [
                'required',
                Rule::unique('proj_modules', 'module_name')
                    ->where(function ($query) use ($request) {
                        return $query->where('proj_id', $request->proj_id);
                    })
            ],
            'proj_id' => 'required',
            'desc' => 'required',
            'emp_id.*' => 'required',
        ]);

        // Create module
        $data = $request->only(['module_name', 'proj_id', 'desc']);
        $module = ProjectModule::create($data);
                $message='<b>The Details are as follows:</b><br/><br/><b>Module Name:</b>'.$request->module_name;
                $message.='<br/><br/>Module Assigned members are as follows:<br/>';
                $message.='<table bordered="1"><th>Name</th><th>Email</th><th>Role</th>';
                
        // Assign members
        foreach ($request->emp_id as $emps) {
            DB::table('module_assign_members')->insert([
                'proj_id'   => $request->proj_id,
                'emp_id'    => $emps,
                'module_id' => $module->id,
            ]);
                $emp_info= User::with('roles')->find($emps);
                $receiver[$emp_info->id] =  [
                        'id' => $emp_info->id,
                        'name' => $emp_info->name ,
                        'email' => $emp_info->email,
                        'role' =>$emp_info->roles[0]['role_name'] ,
                    ];
            $message.='<tr><td>'.$emp_info->name.'</td><td>'.$emp_info->email.'</td><td>'.$emp_info->roles[0]['role_name'].'</td></tr>';
        }
      $message.='</table>';
                     $notify_type="create_project_modules";
                    $subject="Fortgrid - New Project Module created.";
                     $senderMeta =  [
                                        'id' => session('user_id'),
                                        'name' => session('user_name') ,
                                        'email' => session('email'),
                                        'role' => session('role_name') ?? 'User',
                                    ];
                $this->AlertNotifications($notify_type,$senderMeta,$receiver,$subject,$message);      
        // Log activity
        $log_name = 'project_modules';
        ActivityHelper::logActivity('New Project Module Created', $log_name, $module, [
            'request' => $request->all()
        ]);

        return response()->json(['message' => 'Project Module created successfully']);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}
    public function edit(Request $request,$id){
        if (!PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
         }
         $data['module'] = ProjectModule::findOrFail($id);
         $data['module_assign']=ProjectModuleAssign::where('module_id',$id)->pluck('emp_id')->toArray();
         return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($data) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    }
    public function update(Request $request, $id){
    try {
        $module = ProjectModule::findOrFail($id);

        // Validation
        $validated = $request->validate([
            'module_name' => [
                'required',
                Rule::unique('proj_modules', 'module_name')
                    ->where(function ($query) use ($request) {
                        return $query->where('proj_id', $request->proj_id);
                    })
                    ->ignore($id, 'id')
            ],
            'proj_id' => 'required',
            'desc' => 'required',
            'emp_id.*' => 'required',
        ]);

        // Update module data
        $data = $request->only(['module_name', 'proj_id', 'desc']);
        $module->update($data);

        // Refresh assigned members
        DB::table('module_assign_members')->where('module_id', $id)->delete();

        foreach ($request->emp_id as $emps) {
            DB::table('module_assign_members')->insert([
                'proj_id'   => $request->proj_id,
                'emp_id'    => $emps,
                'module_id' => $module->id,
            ]);
        }
        //     $notify_type="create_project_modules";
       //      $subject="Fortgrid - New Project Module created.";
        //     $this->assignTask($task->id,$notify_type,$subject);     
        // Log activity
        $log_name = 'project_modules';
        ActivityHelper::logActivity('Project Module Edited', $log_name, $module, [
            'request' => $request->all()
        ]);

        return response()->json(['message' => 'Project Module Edited successfully']);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
    }
}
 public function destroy($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
        $module = ProjectModule::find($id);
         // Check relations
            if ($module->timesheets()->exists() || 
                $module->pm_tasks()->exists()) {
                return response()->json(['message' => 'Cannot delete: project has related records.'], 400);
            } else {
                if ($module) {
                    $log_name='project_modules';
                    ActivityHelper::logActivity('Project Module Deleted',$log_name, $module, [
                        'request' => request()->all()
                    ]);
                    DB::table('module_assign_members')->where('module_id', $id)->delete();
                    $module->delete();
                    return response()->json(['message' => 'Project Module Deleted successfully!'],200);
                }
            }
        }
    }
public function ModuleAssignEmployees($module_id){
        $module_info = ProjectModuleAssign::with('user')
                                        ->where('module_id', $module_id)
                                        ->get()
                                        ->pluck('user.name','user.id') ->toArray();

        return $module_info;
}

}
