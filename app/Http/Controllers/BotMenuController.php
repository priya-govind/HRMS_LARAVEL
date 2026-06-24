<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BotMenu;
use App\Models\RoleBotPermissions;
use App\Models\Permission;

use App\Helpers\PermissionHelper;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityHelper;

class BotMenuController extends Controller
{
    /**Initiated Global values in construct method refer Controller */
 public function index(Request $request){

  if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
    // Redirect if permission is denied
    return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
}

if ($request->ajax()) {
  $categories = BotMenu::with('bot_parent')->get();
  return DataTables::of($categories)
    ->addIndexColumn()
    ->addColumn('parent_name', function($row) {
      return $row->bot_parent ? $row->bot_parent->bot_name : 'Parent Menu';
    })
         ->addColumn('action', function($row) {
          if($row->is_active==1) {
            $status=' <i class="fa fa-eye" title="active"></i>';
          }  else  {
            $status='  <i class="fa fa-eye-slash"title="inactive"></i>';
          }
          return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
           <button  data-id="'.$row->id.'" data-type="category_status_'.$row->is_active.'" class="btn btn-success btn-sm change_state-btn">'.$status.'

           </button>&nbsp;|&nbsp
            <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>
           ';
      })
      ->rawColumns(['action'])
      ->make(true);
}

$categories_parent = BotMenu::where('parent_id', 1)->get();
$LoadDatatables=true; 
return view('bot_menus.index', compact('categories_parent','LoadDatatables'));
  }
  private function validateBotmenu(Request $request, $id = null)
    {
        return $request->validate([
            'bot_name' => 'required|unique:bot_menus,bot_name,' . ($id ?? 'NULL') . ',id',
            'command' => 'required',
        ]);
    }
  public function store(Request $request)
  {
      // Mass assigment
      $data = $this->validateBotmenu($request);
        $data += $request->only(['parent_id', 'is_active','support_access','service_name','service_method']);
        $data['support_access']=(isset($request->support_access) && $request->support_access==1) ? 1 :0;
        
      $category = BotMenu::create($data);
      $log_type="bot_menu";
   $this->logProcessActivity('Bot Menu created', $category,$log_type);
      $permissions=Permission::select('id')->orderBy('id', 'asc')->get();
      $roleCategoryPermissions = [];
      foreach($permissions as $permission) {
           $roleCategoryPermissions[] = [
               'roles_id' => config('global.superadmin'),
               'bot_id' => $category->id,
               'permission_id' => $permission->id,
           ];
      }
     RoleBotPermissions::insert($roleCategoryPermissions);
      
      return  response()->json(['success' => 'Bot Menu details Added successfully!']);
  }

  public function edit($id){
    $categories= BotMenu::find($id); 
    return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($categories) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    
  }
  public function update(Request $request,$id){
   

 $data = $this->validateBotmenu($request,$id);
        $data += $request->only(['parent_id', 'is_active','support_access','service_name','service_method']);
        $data['support_access']=(isset($request->support_access) && $request->support_access==1) ? 1 :0;
        $menu = BotMenu::find($id);
        $menu->update($data);

 $log_name='bot_menu';
   $this->logProcessActivity('Bot Menu details Edited', $menu,$log_name);
return  response()->json(['success' => 'Bot Menu details updated successfully!','updated_info' =>$data,'updated_Data'=> $menu]);

  }

public function destroy($id){
  $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
 if(!$cat_permission){ 
  return  response()->json(['message' => 'Not Authorized to see this page.'],200);
 }else{
  $user = Auth::user();
  $category = BotMenu::find($id);
if ($category) {
$log_name='bot_menu';
   $this->logProcessActivity('Bot Menu Deleted', $category,$log_name);
    $category->delete();

   RoleBotPermissions::where('bot_id', $id)->delete();
}
  return response()->json(['message' => 'Record Deleted successfully!'],200);
  }
}
public function toggleStatus(Request $request,$id){
  $category=BotMenu::find($id);
  $data['is_active']=1;
   if($request->status=='category_status_1'){
    $data['is_active']=0;
  }
  if($request->status=='category_status_0'){
    $data['is_active']=1;
  }
  $log_name='bot_menu';
   $this->logProcessActivity('Bot Menu Status Changed', $category,$log_name);
  $category->update($data);
  return response()->json(['message' => 'Status Updated successfully!'],200);
}
}
