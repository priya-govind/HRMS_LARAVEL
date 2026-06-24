<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoleCategoryPermission;
use App\Models\Category;
use App\Models\Permission;
use App\Helpers\PermissionHelper;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityHelper;
use Illuminate\Validation\Rule;


class CategoryController extends Controller
{
   
 /**Initiated Global values in construct method refer Controller */
 public function category(Request $request){

  if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
    // Redirect if permission is denied
    return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
}

if ($request->ajax()) {
  $categories = Category::with('parent')->where('id', '!=', config('global.load_category')) ->orderBy('id', 'desc')->get();
  return DataTables::of($categories)
    ->addIndexColumn()
    ->addColumn('parent_name', function($row) {
      return $row->parent ? $row->parent->category_name : 'Parent Category';
    })
         ->addColumn('action', function($row) {
          if($row->is_active_cat==1) {
            $status=' <i class="fa fa-eye" title="active"></i>';
          }  else  {
            $status='  <i class="fa fa-eye-slash"title="inactive"></i>';
          }
          return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
           <button  data-id="'.$row->id.'" data-type="category_status_'.$row->is_active_cat.'" class="btn btn-success btn-sm change_state-btn">'.$status.'

           </button>&nbsp;|&nbsp
            <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>
           ';
      })
      ->rawColumns(['action'])
      ->make(true);
}

$categories_parent = Category::where('parent_id', 1)->get();
$LoadDatatables=true; 
return view('category.category', compact('categories_parent','LoadDatatables'));
  }
  private function validateCategory(Request $request, $id = null)
    {
        return $request->validate([
            'category_name' => [
                                'required',
                                    Rule::unique('category', 'category_name')
                                        ->ignore($id) // ignore current record when updating
                                        ->whereNull('deleted_at'), // exclude soft-deleted rows
                                ],
            'url_link' => 'required',
        ]);
    }
  public function create()
  { 
    $categories= Category::where('parent_id',1)->get(); 
    return  PermissionHelper::checkPermission('global.categories', $this->add_perm) 
    ? view('category.create',compact('categories')) 
     :  redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
  }
  
  public function store(Request $request)
  {
      // $data = $request->validate([
      //     'category_name' => 'required|unique:category',
      //     'url_link' => 'required'
          
      // ]);
      // $data=$request->all();
      // $data['category_name']=$request->category_name;
      // $data['url_link']=$request->url_link;
      // $data['parent_id']=$request->parent_id;
      // $data['is_active_cat']=$request->is_active_cat;

      // Mass assigment
      $data = $this->validateCategory($request);
        $data += $request->only(['parent_id', 'is_active_cat']);
        $data['support_access']=(isset($request->support_access) && $request->support_access==1) ? 1 :0;
        
      $category = Category::create($data);
      $log_type="category";
   $this->logProcessActivity('Category created', $category,$log_type);
      $permissions=Permission::select('id')->orderBy('id', 'asc')->get();
      $roleCategoryPermissions = [];
      foreach($permissions as $permission) {
           $roleCategoryPermissions[] = [
               'roles_id' => config('global.superadmin'),
               'category_id' => $category->id,
               'permission_id' => $permission->id,
           ];
      }
      RoleCategoryPermission::insert($roleCategoryPermissions);
      
      return  response()->json(['success' => 'Category details Added successfully!']);
  }

  public function edit($id){
    $categories= Category::find($id); 
    return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($categories) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    
  }
  public function update(Request $request,Category $category){
    $log_name='category';
   $this->logProcessActivity('Category Edited', $category,$log_name);

    // $data = $request->validate([
    //    'category_name' => 'required|unique:category,category_name,'.$category->id,
    //    'url_link' => 'required'
        
    // ]);
    // $data=$request->all();
    // $data['category_name']=$request->category_name;
    // $data['url_link']=$request->url_link;
    // $data['parent_id']=$request->parent_id;
    // $data['is_active_cat']=$request->is_active_cat;
 $data = $this->validateCategory($request, $category->id);
        $data += $request->only(['parent_id', 'is_active_cat','support_access']);
        $data['support_access']=(isset($request->support_access) && $request->support_access==1) ? 1 :0;
    $category->update($data);
    
//     $category->is_active_cat = $data['is_active_cat'];
// $category->save();

return  response()->json(['success' => 'Category details updated successfully!']);
   // return  redirect()->route('category')
   // ->withSuccess(); 
  }

public function destroy($id){
  $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
 if(!$cat_permission){ 
  return  response()->json(['message' => 'Not Authorized to see this page.'],200);
 }else{
  $user = Auth::user();
  $category = Category::find($id);
if ($category) {
$log_name='category';
   $this->logProcessActivity('Category Deleted', $category,$log_name);
    $category->delete();

    RoleCategoryPermission::where('category_id', $id)->delete();
}
  return response()->json(['message' => 'Record Deleted successfully!'],200);
  }
}
public function status_change($id,Request $request){

  $category=Category::find($id);
  $data['is_active_cat']=1;
   if($request->status=='category_status_1'){
    $data['is_active_cat']=0;
  }
  if($request->status=='category_status_0'){
    $data['is_active_cat']=1;
  }
  $log_name='category';
   $this->logProcessActivity('Category Status Changed', $category,$log_name);
  $category->update($data);
  return response()->json(['message' => 'Status Updated successfully!'],200);
}
public function deleted_category(Request $request){

  if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
    // Redirect if permission is denied
    return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
}
// Fetch all categories if permission is granted

if ($request->ajax()) {
  $categories = Category::onlyTrashed()->get();
  $log_name='category';
   $this->logProcessActivity('View Deleted Category', $categories,$log_name);
  return DataTables::of($categories)
      ->addIndexColumn()
         ->addColumn('action', function($row) {
         
          return '<button type="button"  class="btn btn-dark btn-sm restore-btn" data-id="'.$row->id.'"  data-type="category">
                  <i class="fa fa-refresh" aria-hidden="true"></i></button>';
        })
      ->rawColumns(['action'])
      ->make(true);
}
return view('category.deleted_category',['LoadDatatables' => true]);
}
public function restore_deleted($id){
  $category=Category::withTrashed()->find($id);
  $log_name='category';
   $this->logProcessActivity('Restore Deleted Category', $category,$log_name);
  $category->restore();
  $permissions=Permission::select('id')->orderBy('id', 'asc')->get();
  $roleCategoryPermissions = [];
  foreach($permissions as $permission) {
       $roleCategoryPermissions[] = [
           'roles_id' => config('global.superadmin'),
           'category_id' =>$id,
           'permission_id' => $permission->id,
       ];
  }
  return response()->json(['message' => 'Category Restored successfully!'],200);
}
}