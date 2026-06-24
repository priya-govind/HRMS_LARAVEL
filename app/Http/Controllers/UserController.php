<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Roles;
use App\Models\EmpCertification;
use App\Models\EmpDocs;
use App\Models\EmpExperience;
use App\Models\TeamType;
use App\Models\Attendance;
use App\Models\Leaveinfo;
use App\Models\ProjectModuleAssign;
use App\Models\PMTasksAssign;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\PermissionHelper;
use App\Helpers\ActivityHelper;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Events\NotifyInfo;
use  Barryvdh\DomPDF\Facade\Pdf;


class UserController extends Controller
{
public function index(Request $request, $emp_state = null)
{
    if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
        return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
    }
    $roleId = session('role_id');
    $query = User::query()->where('id', '!=', Auth::id())->where('id', '!=', config('global.superadmin_id'));
    // CEO role filtering
    if (in_array($roleId, config('global.monitor_employees_act')) && (session('support_access')!=1)) {
                    $query->whereHas('roles', function ($q) {
                    $q->whereNotIn('roles.id', config('global.mgmt_team'));
                });
                    $today = Carbon::today('Asia/Kolkata');
                    // $rs=Attendance::whereDate('chkinDate', $today)->pluck('emp_id');
                    // dd($rs);
                // Apply attendance-based filters
                $query->when($emp_state === 'present', function ($q) use ($today) {
                    $presentEmpIds = Attendance::whereDate('chkinDate', $today)->pluck('emp_id');
                    $q->whereIn('id', $presentEmpIds);
                })->when($emp_state === 'PresentTeam', function ($q) use ($today){
                    $teamType = session('team_type');
                    $teamId = session('team_id');

                    if (!empty($teamType)) {
                        $q->whereHas('attendances', function ($query) use ($teamType) {
                            $query->whereIn('team_type', (array) $teamType);
                        });
                    } elseif (!empty($teamId)) {
                        $q->whereHas('team_members', function ($query) use ($teamId) {
                            $query->where('team_id', $teamId);
                        });
                    }
                    $presentEmpIds = Attendance::whereDate('chkinDate', $today)->pluck('emp_id');
                    $q->whereIn('id', $presentEmpIds);
                })->when($emp_state === 'Team', function ($q) {
                    $teamType = session('team_type');
                    $teamId = session('team_id');

                    if (!empty($teamType)) {
                        $q->whereHas('attendances', function ($query) use ($teamType) {
                            $query->whereIn('team_type', (array) $teamType);
                        });
                    } elseif (!empty($teamId)) {
                        $q->whereHas('team_members', function ($query) use ($teamId) {
                            $query->where('team_id', $teamId);
                        });
                    }
                })->when($emp_state === 'absent', function ($q) {
                    $absentEmpIds = Leaveinfo::where('leave_type', 1)
                        ->whereDate('from_dt', '<=', today())
                        ->whereDate('to_dt', '>=', today())
                        ->where('leave_status', config('global.leave_approved'))
                        ->pluck('emp_id');
                    $q->whereIn('id', $absentEmpIds);
                })->when($emp_state === 'permission', function ($q) {
                    $permissionEmpIds = Leaveinfo::where('leave_type', 2)
                        ->whereDate('from_dt', today())
                        ->where('leave_status', config('global.leave_approved'))
                        ->pluck('emp_id');
                    $q->whereIn('id', $permissionEmpIds);
                })->when($emp_state === 'late', function ($q) {
                    $lateEmpIds = Attendance::whereDate('chkinDate', today())
                        ->whereTime('chkinDate', '>', '09:30')
                        ->pluck('emp_id');
                    $q->whereIn('id', $lateEmpIds);
                });
    } elseif (in_array($roleId, config('global.first_level_role')) && (session('support_access')!=1)) {
        $query->whereHas('roles', function ($q) {
            $q->whereNotIn('roles.id', config('global.mgmt_team'));
        });
    }  
    $rs=$query->with('roles')->orderBy('id', 'desc');
         //     $sql = vsprintf(
        //         str_replace('?', "'%s'", $tasks->toSql()),
        //         $tasks->getBindings()
        //     );
        //   echo $sql;
        //   die;
    $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
    $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);

if ($request->ajax()) {
    return DataTables::of($rs)
        ->addIndexColumn()
        ->addColumn('roles', fn($row) => $row->roles->pluck('role_name')->implode(', '))
        ->addColumn('action', function ($row) use ($edit_permit, $delete_permit) {
            $statusIcon = $row->emp_status == config('global.active_status')
                ? '<i class="fa fa-eye" title="active"></i>'
                : '<i class="fa fa-eye-slash" title="inactive"></i>';

            $editButton = $edit_permit
                ? '<a href="' . route("employees.edit", $row->id) . '" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>'
                : '';

            $changeStateButton = $edit_permit
                ? '&nbsp;|&nbsp;<button data-id="' . $row->id . '" data-status="' . $row->emp_status . '" class="btn btn-success btn-sm change_state-btn">' . $statusIcon . '</button>'
                : '';

            $deleteButton = $delete_permit
                ? '&nbsp;|&nbsp;<a href="#" class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '"><i class="fa fa-trash-o"></i></a>'
                : '';
            
             $viewButton = '&nbsp;|&nbsp;<button data-id="' . $row->id . '"  class="btn btn-primary btn-sm ProjectsButton" title="Projects Assigned"><i class="fas fa-project-diagram"></i></button>
                            &nbsp;|&nbsp;
                            <button data-id="' . $row->id . '"  class="btn btn-success btn-sm TasksButton" title="Tasks Assigned">	<i class="fas fa-tasks"></i></button>';
            
            $spacer=(session('support_access')==1 && $delete_permit) ? '&nbsp;|&nbsp;' : '';
            $generateProfileButton=(session('role_id')== config('global_roles.HR')) ? '&nbsp;|&nbsp;<a href="employees/'.$row->id.'/profile/download" class="btn btn-primary btn-sm" title="Generate Profile"> <i class="fa-solid fa-file"></i> </button>' : '';
            //$viewButton
            return $editButton . $changeStateButton . $deleteButton.$generateProfileButton;
        })
        ->rawColumns(['action'])
        ->make(true);
}
    return view('employees.index', ['LoadDatatables' => true,'edit_permit' => $edit_permit,'delete_permit' => $delete_permit]);
}

    public function create(){
        if (!PermissionHelper::checkPermission('global.categories', $this->add_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $roles=Roles::all();
        return view('employees.create',compact('roles'));
       
    }
    public function store(Request $request){
      $request->validate([
            'name' => 'required|regex:/^[a-zA-Z0-9\s]+$/|unique:users,name',
            'email' => 'required|unique:users,email|email', 
            'password' => 'min:8|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'min:8',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'emp_status'=>'required',
            'address' =>'required',
            'role_id' => 'required|exists:roles,id',
          ], );
          $imageName = time().'.'.$request->image->extension();
          $request->image->move(public_path('images'), $imageName);
      
          $user = User::create([
            'name' => $request->name,
            'email' =>$request->email,
            'password' =>Hash::make($request->password),
            'address' =>$request->address,
            'emp_status'=>$request->emp_status,
            'image' => $imageName,
            ]);
            $info['notify_type']="user_register";
            $info['user']=$user;
         if ($user) {
                event(new NotifyInfo($info));
            }
           event(new NotifyInfo($user));
            if ($request->role_id) {
                $user->roles()->sync($request->role_id);
            }
          $new_id=$user ->id;
          $log_name='employees';
          ActivityHelper::logActivity('Create New Employee',$log_name, $user, [
            'request' => request()->all()
        ]);
          return redirect()->route('employees')->withSuccess('User Added successfully!');
      }
      public function edit($id){
        if (!PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
                  $user = User::findOrFail($id);
                  $roles=Roles::all();
                  $userRoles = $user->roles->pluck('id')->toArray();
                  return view('employees.edit', compact('user','roles','userRoles'));
          }
      
          public function update(Request $request,$id)
          {
           $user=User::find($id);
            $validatedData = $request->validate([
                        'name' => 'required|regex:/^[a-zA-Z0-9\s]+$/|unique:users,name,' . $user->id,
                        'email' => 'required|email|unique:users,email,' . $user->id, 
                        'emp_status'=>'required',
                        'address' =>'required',
                        'role_id' => 'required|exists:roles,id',
                    ], );
              
              // Handle image upload
              if ($request->hasFile('image')) {
                  if ($user->image) {
                      $oldImagePath = public_path('images/' . $user->image);
                      if (file_exists($oldImagePath)) {
                          unlink($oldImagePath);
                      }
                  }
                  $imageName = time() . '.' . $request->image->extension();
                  $request->image->move(public_path('images'), $imageName);
                  $validatedData['image'] = $imageName;
              } else {
                  $validatedData['image'] = $user->image;
              }
            $result= $user->update($validatedData);
             // @dd($request->role_id);
              $user->roles()->sync(array_map('intval', $request->role_id));
              $new_id=$user ->id;            
          $log_name='employees';
          ActivityHelper::logActivity('Update Employee',$log_name, $user, [
            'request' => request()->all()
        ]);
          // Redirect with success message
              $route = 'employees';
              return redirect()->route($route)->withSuccess('Employee Details updated successfully!');
          }
public function destroy($id){
    if (!PermissionHelper::checkPermission('global.categories', $this->del_perm)) {
        // Redirect if permission is denied
        return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
    }
    $users = User::find($id);
    $log_name='employees';
    ActivityHelper::logActivity('Delete Employee',$log_name, $users, [
      'request' => request()->all()
  ]);
    $users->delete();  
return response()->json(['message' => 'Employee Deleted successfully!'],200); 
}
public function show(){
    $cu_user = Auth::user();

$user=User::find($cu_user->id);
$roles=Roles::all();
$userRoles = $user->roles->pluck('id')->toArray();
    return view('my_profile', compact('user','roles','userRoles'));
}
    public function deleted_employees(Request $request){  
      
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        // Fetch all categories if permission is granted
        
        if ($request->ajax()) {
            $data = $users = User::onlyTrashed()
                    ->get();
                    
                    $log_name='employees';
                    ActivityHelper::logActivity('Deleted Employees',$log_name, $data, [
                      'request' => request()->all()
                    ]);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return ' <button type="button"  class="btn btn-dark btn-sm restore-btn" data-id="'.$row->id.'"  data-type="user">
                                    <i class="fa fa-refresh" aria-hidden="true"></i></button>';
        
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('employees.deleted_employees',['LoadDatatables' => true]);
    }
    public function restore_deleted($id){
        $user=User::withTrashed()->find($id);
        $log_name='employees';
        ActivityHelper::logActivity('Restore Deleted Employee',$log_name, $user, [
          'request' => request()->all()
        ]);
        $user->restore();
         return response()->json(['message' => 'Employee Restored successfully!'],200);
        }
public function status_change($id,Request $request){

  $category=User::find($id);
  if($request->status==config('global.active_status')){ //1 ->0
    $data['emp_status']=config('global.inactive_status');
  }
  if($request->status==config('global.inactive_status')){ //0=>1
    $data['emp_status']=config('global.active_status');
  }
  $log_name='category';
  ActivityHelper::logActivity('Category Status Changed', $log_name , $category, [
    'request' => request()->all()
]);

  $category->update($data);
  
 
  return response()->json(['message' => 'Status Updated successfully!'],200);
}
public function downloadProfile($id)
{
    $user = User::with(['roles'])->findOrFail($id);

    $pdf = Pdf::loadView('employees.profile_pdf', compact('user'));
    return $pdf->download('user_profile_'.str_replace(' ','_',$user->name).'.pdf');
}
public function previewProfile($id)
{
    $user = User::with(['roles'])->findOrFail($id);
    return view('employees.profile_pdf', compact('user'));
}
public function AssignedProjects($id)
{
    $proj_info = ProjectModuleAssign::with('projects')->where('emp_id',$id)->get();
   $projects = $proj_info->map(function ($assign) {
        return $assign->projects->proj_name;   // each assign has a related project
    });

    return $projects;
}
public function AssignedTasks($id){
    $task_info=PMTasksAssign::with('task')->where('employee_id',$id)->get();
    $tasks=$task_info->map(function($task_col){
        return $task_col->task->task_name;
    });
    return $tasks;
}
}
