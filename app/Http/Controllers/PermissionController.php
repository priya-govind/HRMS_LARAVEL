<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Category;
use App\Helpers\PermissionHelper;
use App\Helpers\ActivityHelper;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
  /**Initiated Global values in construct method refer Controller */   
    public function permissions(Request $request){
      $cat_permission= PermissionHelper::checkPermission('global.categories',$this->view_perm);
      if(!$cat_permission){ 
        return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
      }
        
        if ($request->ajax()) {
          $permissions = Permission::get();
          return DataTables::of($permissions)
              ->addIndexColumn()
                 ->addColumn('action', function($row) {
                  if($row->is_active_cat==1) {
                    $status=' <i class="fa fa-eye" title="active"></i>';
                  }  else  {
                    $status='  <i class="fa fa-eye-slash"title="inactive"></i>';
                  }
                  return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
                    <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>
                   ';
              })
              ->rawColumns(['action'])
              ->make(true);
        }


        return view('permissions.permissions',['LoadDatatables' => true]);
    
      }
      public function create_permission()
      {
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->add_perm);
        if(!$cat_permission){ 
          return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }else{
            return view('permissions.create_permission');
        }
      }
      public function store_permission(Request $request)
      {
          $validated = $request->validate([
              'permission_name' => 'required|unique:permissions' 
          ]);
          
         $data = $request->except('_token');
          // Mass assigment
         $permission= Permission::create($data);
          $log_name='permission';
          ActivityHelper::logActivity('Create New Permission',$log_name, $permission, [
            'request' => request()->all()
        ]);
          return  response()->json(['success' => 'Permission details Added successfully!']);
          // return redirect()->route('permissions')
          //         ->withSuccess('New Permission created successfully.');
      }
   
      public function edit_permission(Permission $permission){

        return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($permission) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
      
      }
      public function update_permission(Request $request,Permission $permission){
        $request->validate([
           'permission_name' => 'required|unique:permissions,permission_name,'.$permission->id,
        ]);
        $log_name='permission';
          ActivityHelper::logActivity('Edit Permission',$log_name, $permission, [
            'request' => request()->all()
        ]);
        $data = $request->all();
        $permission->update($data);
        return  response()->json(['success' => 'Permission details Updated successfully!']);

      }
      public function destroy($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
       if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
       }else{
       
        $permission = Permission::find($id);

        $log_name='permission';
        ActivityHelper::logActivity('Delete Permission',$log_name, $permission, [
          'request' => request()->all()
        ]);

        $permission->delete();
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
      }
      public function deleted_permissions(Request $request){
          if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        // Fetch all categories if permission is granted
       
        if ($request->ajax()) {
          $permissions = Permission::onlyTrashed()->get();
          
          $log_name='permission';
          ActivityHelper::logActivity('View Deleted Permission',$log_name, '', [
            'request' => request()->all()
        ]);



          return DataTables::of($permissions)
              ->addIndexColumn()
                 ->addColumn('action', function($row) {
                  
                  return '<button type="button"  class="btn btn-dark btn-sm restore-btn" data-id="'.$row->id.'"  data-type="permission">
                          <i class="fa fa-refresh" aria-hidden="true"></i></button>';
              })
              ->rawColumns(['action'])
              ->make(true);
        }
        
        return view('permissions.deleted_permissions',['LoadDatatables' => true]);
        }
        public function restore_deleted($id){
          $permissions=Permission::withTrashed()->find($id);
          $log_name='permission';
          ActivityHelper::logActivity('Restore Deleted Permission',$log_name, $permissions, [
            'request' => request()->all()
        ]);
          $permissions->restore();
    return response()->json(['message' => 'Permission Restored successfully!'],200);
        }
}
