<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectStatus;
use App\Models\ProjectType;
use App\Models\Projects;
use App\Models\ProjectModule;
use App\Models\ProjectModuleAssign;
use App\Helpers\ActivityHelper;
use App\Helpers\PermissionHelper;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;


use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProjectController extends Controller
{
    public function project_status(Request $request){
        $proj_types = ProjectStatus::get();
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->view_perm);
        if(!$cat_permission){ 
          return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
      $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
      $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);    
          if ($request->ajax()) {
            $proj_types = ProjectStatus::get();
            return DataTables::of($proj_types)
                ->addIndexColumn()
                   ->addColumn('action', function($row) use ($edit_permit, $delete_permit) {
                    $editButton =  $edit_permit
                            ? '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;' 
                            : '';
                      $deleteButton = $delete_permit 
                                ? '<button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>' 
                                : '';       
                    return $editButton.$deleteButton;
                })
                ->rawColumns(['action'])
                ->make(true);
          }
          return view('tasks.project_status',['LoadDatatables' => true]);
    }
    public function edit_proj_status($id){
            $proj_types= ProjectStatus::find($id); 
            return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($proj_types) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    }

    public function store_proj_status(Request $request){
          $validated = $request->validate([
              'proj_status_name' => [
                                  'required',
                                  Rule::unique('project_status')->whereNull('deleted_at')
                                 ],
          ]);
         $data['emp_set_status']=$request->emp_set_status ?  $request->emp_set_status : 0;
         $data['task_set_status']=$request->task_set_status ?  $request->task_set_status : 0;
         $data['proj_set_status']=$request->proj_set_status ?  $request->proj_set_status : 0;
         $data = $request->except('_token');
         
          // Mass assigment
         $proj_types= ProjectStatus::create($data);
      
          $log_name='projects';
          ActivityHelper::logActivity('Create New Status Name',$log_name, $proj_types, [
            'request' => request()->all()
        ]);
          return response()->json(['message' => 'New Status Name Added successfully!'],200);
          // return redirect()->route('permissions')
          //         ->withSuccess('New Permission created successfully.');
    }
    public function update_proj_status(Request $request){
        $proj_types= ProjectStatus::find($request->id);
        
        $request->validate([
           'proj_status_name' =>  [
                                  'required',
                                  Rule::unique('project_status', 'proj_status_name')
                                      ->ignore($request->id)       // allow current record
                                      ->whereNull('deleted_at')    // exclude soft-deleted rows
                              ],
        ]);
        $log_name='projects';
          ActivityHelper::logActivity('Status Name Updated Successfully',$log_name, $proj_types, [
            'request' => request()->all()
        ]);
        $data['emp_set_status']= isset($request->emp_set_status) ?  $request->emp_set_status : 0;
         $data['task_set_status']=isset($request->task_set_status) ?  $request->task_set_status : 0;
         $data['proj_set_status']=isset($request->proj_set_status) ?  $request->proj_set_status : 0;
         $data = $request->except('_token');
        
        $proj_types->update($data);
        
        return  response()->json(['message' => 'Status Name Updated successfully!'],200);

    }
    public function destroy_project_status($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
       if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
       }else{
        $user = Auth::user();
        $proj_types= ProjectStatus::find($id);
      if ($proj_types) {
      $log_name='projects';
           ActivityHelper::logActivity('Status Name Deleted',$log_name, $proj_types, [
                      'request' => request()->all()
                  ]);
          $proj_types->delete();
      }
      
      
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }

    public function manage_projects(Request $request,$status = null){
      if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
          // Redirect if permission is denied
          return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
      }  
      $LoadDateTimepicker = true;
      $project_status=  ProjectStatus::get();
       $LoadDatatables=true;  
       $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
       $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);  
      if ($request->ajax()) {
        $query_main= Projects::with('status');
        if($status!=''&& $status=='active'){
           $query_main->whereHas('pm_tasks', function ($query) {
                $query->whereNotIn('task_status', [config('global.completed_status')]);
            });
        }
        $query_main->select('projects.*');
        $projects=$query_main->orderBy('id', 'desc')->get();

        return DataTables::of($projects)
            ->addIndexColumn()
            ->addColumn('start_date', function($row){
                  return $row->start_date 
                      ? Carbon::parse($row->start_date)->format('d-m-Y') 
                      : '';
              })
              ->addColumn('end_date', function($row){
                  return $row->end_date 
                      ? Carbon::parse($row->end_date)->format('d-m-Y') 
                      : '';
              })
           ->addColumn('action', function($row) use ($edit_permit, $delete_permit) {
               $editButton = $edit_permit
                            ? '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>' 
                            : '';
               $deleteButton = $delete_permit
                                ? '&nbsp;|&nbsp;<button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>' 
                                : '';

                return $editButton . $deleteButton;
            })
            ->rawColumns(['action'])
            ->make(true);
      }
      return view('tasks.manage_projects', compact('project_status','LoadDatatables','LoadDateTimepicker'));
  }
  public function store_projects(Request $request)
  {
      $data = $request->validate([
        'proj_name' => [
                        'required',
                        Rule::unique('projects')->whereNull('deleted_at')
                      ],
          'proj_desc' =>'required',
          'start_date' =>'required' ,
          'end_date' =>'required',       
          'proj_status' =>'required',
          'proj_color' => 'required',
          
      ]);
      $data=$request->all();
      $data['proj_name']=$request->proj_name;
      $data['start_date']=Carbon::createFromFormat('d-m-Y', $request->start_date)
                                                ->format('Y-m-d');
      $data['end_date']=Carbon::createFromFormat('d-m-Y', $request->end_date)
                                                ->format('Y-m-d');
      $data['proj_status']=$request->proj_status;

      // Mass assigment
      $project = Projects::create($data);
      $log_name='projects';
      ActivityHelper::logActivity('New Project created',$log_name, $project, [
        'request' => request()->all()
    ]);
      return  response()->json(['message' => 'Project details Added successfully!']);
  }
  public function edit_project($id){
    $projects= Projects::find($id); 
    return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($projects) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    
  }
  public function update_project(Request $request,$id){
    $project=Projects::find($id); 
    $log_name='projects';
    ActivityHelper::logActivity('Project Edited',$log_name, $project, [
      'request' => request()->all()
  ]);
  
    // Define validation rules
$data = $request->validate([
                            'proj_name' => [
                                'required',
                                'string',
                                'max:255',
                                Rule::unique('projects', 'proj_name')
                                    ->ignore($project->id)        // allow current record
                                    ->whereNull('deleted_at')     // exclude soft-deleted rows
                            ],
                             'proj_desc' =>'required',
                              'start_date' =>'required' ,
                              'end_date' =>'required',   
                            'proj_status' => 'required|string|max:50',
                            'proj_color' => 'required',
                        ]);
    $data=$request->all();
    $data['proj_name']=$request->proj_name;
    $data['start_date']=Carbon::createFromFormat('d-m-Y', $request->start_date)
                                                ->format('Y-m-d');
      $data['end_date']=Carbon::createFromFormat('d-m-Y', $request->end_date)
                                                ->format('Y-m-d');
    $data['proj_status']=$request->proj_status;

    $project->update($data);
return  response()->json(['message' => 'Project details updated successfully!']);
   
  }
public function destroy_project($id)
{
    $cat_permission = PermissionHelper::checkPermission('global.categories', $this->del_perm);

    if (!$cat_permission) {
        return response()->json(['message' => 'Not Authorized to see this page.'], 200);
    }

    $projects = Projects::find($id);

    if (!$projects) {
        return response()->json(['message' => 'Project not found.'], 404);
    }

    // Check relations
    if ($projects->modules()->exists() || 
        $projects->timesheets()->exists() || 
        $projects->pm_tasks()->exists()) {
        return response()->json(['message' => 'Cannot delete: project has related records.'], 400);
    } else {
       // Log activity
    $log_name = 'projects';
    ActivityHelper::logActivity('Project Deleted', $log_name, $projects, [
        'request' => request()->all()
    ]);
    $projects->delete();
    return response()->json(['message' => 'Record Deleted successfully!'], 200);
    }
   
}
  public function getProjects($type){
    $projects = Projects::where('proj_type', $type)->pluck('proj_name', 'id'); // Adjust based on your database structure
    return response()->json($projects);
  }
  public function getProjectModules($proj_id){
    $modules = ProjectModule::where('proj_id', $proj_id)->pluck('module_name', 'id'); // Adjust based on your database structure
    return response()->json($modules);
  }
  public function getAssignProjMembers($module_id){
     $emps = ProjectModuleAssign::with('user')
                      ->where('module_id', $module_id)
                      ->whereHas('user', function ($query) {
                          $query->where('emp_status', config('global.active_status'));
                      })
                      ->get()
                      ->mapWithKeys(function ($assign) {
                          return [$assign->emp_id => $assign->user->name];
                      });

    return response()->json($emps);


}
public function getProjectsAll(Request $request) {
    $search = $request->input('term'); // This is what Select2 sends
    $projects = Projects::where('proj_name', 'like', "%{$search}%")
                        ->select('id', 'proj_name')
                        ->get();
    return response()->json($projects);
}
public function getProjectsById($id){
      $project = Projects::find($id);
      return $project ? response()->json($project) : response()->json(['error' => 'Not found'], 404);
}

  public function deleted_projects(Request $request){

  if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
    // Redirect if permission is denied
    return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
}

if ($request->ajax()) {
  $projects = Projects::onlyTrashed()->get();
  $log_name='projects';
  ActivityHelper::logActivity('View Deleted Projects',$log_name, '', [
    'request' => request()->all()
]);


  return DataTables::of($projects)
      ->addIndexColumn()
         ->addColumn('action', function($row) {
         
          return '<button type="button"  class="btn btn-dark btn-sm restore-btn" data-id="'.$row->id.'">
                  <i class="fa fa-refresh" aria-hidden="true"></i></button>';
        })
      ->rawColumns(['action'])
      ->make(true);
}
$LoadDatatables=true; 
     
return view('tasks.deleted_projects',compact('LoadDatatables'));
  }
  public function restore_deleted($id){
  $tasks=Projects::withTrashed()->find($id);
  $log_name='projects';
  ActivityHelper::logActivity('Restore Deleted Projects',$log_name, $tasks, [
    'request' => request()->all()
]);
  $tasks->restore();
  return response()->json(['message' => 'Project Restored successfully!'],200);
}
public function getMappedEmployees($proj_id){
   $modules = ProjectModuleAssign::with('user')
                                  ->where('proj_id', $proj_id)
                                  ->get()
                                  ->pluck('user')
                                  ->unique('id')   
                                  ->pluck('name','id');
    return response()->json($modules);
  }
}
