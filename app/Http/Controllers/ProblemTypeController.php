<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoleCategoryPermission;
use App\Models\ProblemType;
use App\Models\TicketType;
use App\Helpers\PermissionHelper;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class ProblemTypeController extends Controller
{
         /**Initiated Global values in construct method refer Controller */
 public function index(Request $request){

    if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
        // Redirect if permission is denied
        return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
    }
        $problem_types = ProblemType::get();
        if ($request->ajax()) {
        $problem_types = ProblemType::get();
        return DataTables::of($problem_types)
            ->addIndexColumn()
                ->addColumn('action', function($row) {
                if($row->problem_type_active==1) {
                    $status=' <i class="fa fa-eye" title="active"></i>';
                }  else  {
                    $status='  <i class="fa fa-eye-slash"title="inactive"></i>';
                }
                return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
                <button  data-id="'.$row->id.'" data-type="category_status_'.$row->problem_type_active.'" class="btn btn-success btn-sm change_state-btn">'.$status.'

                </button>&nbsp;|&nbsp
                    <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        $LoadDatatables=true; 
        $ticket_type = TicketType::get();
        return view('tickets.problem_types', compact('LoadDatatables','ticket_type'));
  }
  private function validateProblemType(Request $request, $id = null)
    {
        return $request->validate([
            'problem_type' => 'required|unique:problem_types,problem_type,' . ($id ?? 'NULL') . ',id',
        ]);
    }
  public function create()
  { 
    $problem_types= ProblemType::get(); 
    return  PermissionHelper::checkPermission('global.categories', $this->add_perm) 
    ? view('tickets.create') 
     :  redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
  }
  
  public function store(Request $request)
  {
      // $data = $request->validate([
      //     'problem_type' => 'required|unique:category',
      //     'url_link' => 'required'
          
      // ]);
      // $data=$request->all();
      // $data['problem_type']=$request->problem_type;
      // $data['url_link']=$request->url_link;
      // $data['parent_id']=$request->parent_id;
      // $data['problem_type_active']=$request->problem_type_active;

      // Mass assigment
      $data = $this->validateProblemType($request);
        $data += $request->only(['ticket_type_id','problem_type','problem_type_active']);
        
      $category = ProblemType::create($data);
      $log_type="problem_type";
   $this->logProcessActivity('Problem Type created', $category,$log_type);    
      return  response()->json(['success' => 'Problem Type details Added successfully!']);
  }

  public function edit($id){
    $problem_types= ProblemType::find($id); 
    return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($problem_types) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    
  }
  public function update(Request $request){
       $problem_types= ProblemType::find($request->id);
    $log_name='problem_type';
   $this->logProcessActivity('Problem Type Edited', $problem_types,$log_name);
    $data = $this->validateProblemType($request, $problem_types->id);
        $data += $request->only(['ticket_type_id','problem_type','problem_type_active']);
    $problem_types->update($data);
    
return  response()->json(['success' => 'Problem Type details updated successfully!']);
  }

public function destroy($id){
  $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
 if(!$cat_permission){ 
  return  response()->json(['message' => 'Not Authorized to see this page.'],200);
 }else{
  $user = Auth::user();
  $category = ProblemType::find($id);
if ($category) {
$log_name='ticket_type';
   $this->logProcessActivity('Problem Type Deleted', $category,$log_name);
    $category->delete();

    RoleCategoryPermission::where('category_id', $id)->delete();
}
  return response()->json(['message' => 'Record Deleted successfully!'],200);
  }
}
public function status_change($id,Request $request){
  $category=ProblemType::find($id);
  if($request->status=='category_status_1'){
    $data['problem_type_active']=0;
  }
  if($request->status=='category_status_0'){
    $data['problem_type_active']=1;
  }
  $log_name='category';
   $this->logProcessActivity('Problem Type Status Changed', $category,$log_name);
  $category->update($data);
  return response()->json(['message' => 'Status Updated successfully!'],200);
}
 public function get_problem_type($id){
    $problem_types = ProblemType::where('ticket_type_id', $id)->pluck('problem_type', 'id'); 
    return response()->json($problem_types);
  }
}
