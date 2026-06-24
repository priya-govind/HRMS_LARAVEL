<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Roles;
use App\Models\Category;
use App\Models\Permission;
use App\Models\RoleCategoryPermission;
use App\Models\BotMenu;
use App\Models\RoleBotPermissions;
use App\Helpers\PermissionHelper;
use App\Helpers\ActivityHelper;
use Yajra\DataTables\Facades\DataTables;
class RoleController extends Controller
{
 /**Initiated Global values in construct method refer Controller */
 public function roles(Request $request)
 {
     if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
         // Redirect if permission is denied
         return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
     }
     if ($request->ajax()) {
         $roles = Roles::where('id','!=',config('global_roles.Super Admin'))->get();
         return DataTables::of($roles)
             ->addIndexColumn()
             ->addColumn('action', function($row) {
                 return '<a href="' . route('roles.edit', $row->id) . '" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>&nbsp;|&nbsp; <a href="' . route('roles.edit_bot_permission', $row->id) . '" class="btn btn-primary btn-sm"> <i class="fa-solid fa-robot"></i></a>';
             })
             ->rawColumns(['action'])
             ->make(true);
     }
     return view('roles.roles',['LoadDatatables' => true]);
 }
      public function create_role()
      {
        if (!PermissionHelper::checkPermission('global.categories', $this->add_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }else{
            $permissions=Permission::select('id','permission_name')->orderBy('order_by', 'asc')->get();
                // Fetch all parent categories
                if(session('role_id')==17){
                    //super admin all categroies display
                    $categories = Category::where('parent_id', 1)
                                            ->where('is_active_cat', 1) ->get();
                } else {
                    $categories = Category::where('parent_id', 1)->where('is_active_cat', 1) ->where('id', '!=', 8)->get();
                }
                // Eager load child categories
                foreach ($categories as $category) {
                    if(session('role_id')==17){
                        //super admin all categroies display
                        $category->children = Category::where('parent_id', $category->id)->get();
                    } else {
                        $category->children = Category::where('parent_id', $category->id)->where('id', '!=', 8)->get();
                    } 
                }
            //$pages=Category::select('id','category_name','url_link')->get();
            return view('roles.create_role', compact('permissions','categories'));
        }
      }
      public function store_role(Request $request)
      {
          $validated = $request->validate([
              'role_name' => 'required|unique:roles' 
          ]);
         $data = $request->except('_token');
          // Mass assigment
          $newRecord= Roles::create($data);
          $roleId= $newRecord->id;
          $permissions = $request->input('permissions', []);
          // Insert new permissions
          foreach ($permissions as $catId => $permissionIds) {
              foreach ($permissionIds as $permissionId => $value) {
                RoleCategoryPermission::create([
                      'roles_id' => $roleId,
                      'category_id' => $catId,
                      'permission_id' => $permissionId,
                  ]);
              }
          }
          $log_name='roles';
          ActivityHelper::logActivity('Create New Role',$log_name, $newRecord, [
            'request' => request()->all()
        ]);
          return redirect()->route('roles')
                  ->withSuccess('New Role created successfully.');
      }
      public function edit_role(Roles $role){
        $page_permission= Category::SecureUser($this->load_roles_page,$this->edit_perm);
        //verifies logged in user can access the page
            if(!$page_permission){ 
                    return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
            }else{
                $roleId=$role->id;
                $permissions=Permission::select('id','permission_name')->orderBy('order_by', 'asc')->get();
                if(session('role_id')==17){
                    //super admin all categroies display
                    $categories = Category::where('parent_id', 1)->where('is_active_cat', 1) ->get();
                } else {
                    $categories = Category::where('parent_id', 1)->where('is_active_cat', 1) ->where('id', '!=', 8)->get();
                }
                // Eager load child categories
                foreach ($categories as $category) {
                    if(session('role_id')==17){
                        //super admin all categroies display
                        $category->children = Category::where('parent_id', $category->id)->where('is_active_cat', 1) ->get();
                    } else {
                        $category->children = Category::where('parent_id', $category->id)->where('is_active_cat', 1) ->where('id', '!=', 8)->get();
                    }
                }
                $existingPermissions = RoleCategoryPermission::where('roles_id', $roleId)
                ->get()
                ->groupBy('category_id')
                ->map(function ($group) {
                    return $group->pluck('permission_id')->toArray();
                });
                return view('roles.edit_role', compact('permissions','categories','existingPermissions','role'));
            }
      }
      public function update_role(Request $request,Roles $role){
        $request->validate([
           'role_name' => 'required|unique:roles,role_name,'.$role->id,
        ]);
        $data = $request->all();
        $role->update($data);
        $log_name='roles';
        ActivityHelper::logActivity('Edit Role',$log_name, $role, [
          'request' => request()->all()
      ]);
        $roleId = $role->id;
        $permissions = $request->input('permissions', []);
   // @dd($permissions);
        // Delete existing permissions for the role
        RoleCategoryPermission::where('roles_id', $roleId)->delete();
        // Insert new permissions
        foreach ($permissions as $pageId => $permissionIds) {
            foreach ($permissionIds as $permissionId => $value) {
                RoleCategoryPermission::create([
                    'roles_id' => $roleId,
                    'category_id' => $pageId,
                    'permission_id' => $permissionId,
                ]);  
            }
        }
        return redirect()->route('roles')->with('success', 'Permissions updated successfully!');
      }
      public function edit_bot(Roles $role){
             if (!PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
                    return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
              }else{
                $roleId=$role->id;
                $permissions=Permission::select('id','permission_name')->orderBy('order_by', 'asc')->get();
                $categories = BotMenu::where('parent_id', 1)->where('is_active', 1) ->get();
                
                // Eager load child categories
                foreach ($categories as $category) {
                        $category->bot_children = BotMenu::where('parent_id', $category->id)->where('is_active', 1) ->get();
                }
                $existingPermissions = RoleBotPermissions::where('roles_id', $roleId)
                ->get()
                ->groupBy('bot_id')
                ->map(function ($group) {
                    return $group->pluck('permission_id')->toArray();
                });
                return view('roles.chatbot_permissions', compact('permissions','categories','existingPermissions','role'));
            }

      }
       public function update_bot_permission(Request $request,Roles $role){
            $request->validate([
            'role_name' => 'required|unique:roles,role_name,'.$role->id,
            ]);
            $data = $request->all();
            $role->update($data);
            $log_name='roles';
            ActivityHelper::logActivity('Edit Role',$log_name, $role, [
            'request' => request()->all()
        ]);
            $roleId = $role->id;
            $permissions = $request->input('permissions', []);
    // @dd($permissions);
            // Delete existing permissions for the role
            RoleBotPermissions::where('roles_id', $roleId)->delete();
            // Insert new permissions
            foreach ($permissions as $pageId => $permissionIds) {
                foreach ($permissionIds as $permissionId => $value) {
                    RoleBotPermissions::create([
                        'roles_id' => $roleId,
                        'bot_id' => $pageId,
                        'permission_id' => $permissionId,
                    ]);  
                }
            }
            return redirect()->route('roles')->with('success', 'Permissions updated successfully!');
        }
}
