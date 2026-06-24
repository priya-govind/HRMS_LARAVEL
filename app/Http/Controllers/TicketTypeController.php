<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoleCategoryPermission;
use App\Models\TicketType;
use App\Models\Permission;
use App\Helpers\PermissionHelper;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class TicketTypeController extends Controller
{
     /**Initiated Global values in construct method refer Controller */
 public function index(Request $request){

  if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
    // Redirect if permission is denied
    return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
}
$ticket_types = TicketType::get();
if ($request->ajax()) {
  $ticket_types = TicketType::get();
  return DataTables::of($ticket_types)
      ->addIndexColumn()
         ->addColumn('action', function($row) {
          if($row->ticket_type_active==1) {
            $status=' <i class="fa fa-eye" title="active"></i>';
          }  else  {
            $status='  <i class="fa fa-eye-slash"title="inactive"></i>';
          }
          return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
           <button  data-id="'.$row->id.'" data-type="category_status_'.$row->ticket_type_active.'" class="btn btn-success btn-sm change_state-btn">'.$status.'

           </button>&nbsp;|&nbsp
            <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>
           ';
      })
      ->rawColumns(['action'])
      ->make(true);
}
$LoadDatatables=true; 
return view('tickets.ticket_types', compact('LoadDatatables'));
  }
  private function validateTicketType(Request $request, $id = null)
    {
        return $request->validate([
            'ticket_type' => 'required|unique:ticket_types,ticket_type,' . ($id ?? 'NULL') . ',id',
        ]);
    }
  public function create()
  { 
    $ticket_types= TicketType::get(); 
    return  PermissionHelper::checkPermission('global.categories', $this->add_perm) 
    ? view('tickets.create') 
     :  redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
  }
  
  public function store(Request $request)
  {
      // $data = $request->validate([
      //     'ticket_type' => 'required|unique:category',
      //     'url_link' => 'required'
          
      // ]);
      // $data=$request->all();
      // $data['ticket_type']=$request->ticket_type;
      // $data['url_link']=$request->url_link;
      // $data['parent_id']=$request->parent_id;
      // $data['ticket_type_active']=$request->ticket_type_active;

      // Mass assigment
      $data = $this->validateTicketType($request);
        $data += $request->only(['ticket_type','ticket_type_active']);
        
      $category = TicketType::create($data);
      $log_type="ticket_type";
   $this->logProcessActivity('Ticket Type created', $category,$log_type);    
      return  response()->json(['success' => 'Ticket Type details Added successfully!']);
  }

  public function edit($id){
    $ticket_types= TicketType::find($id); 
    return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($ticket_types) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    
  }
  public function update(Request $request){
       $ticket_types= TicketType::find($request->id);
    $log_name='ticket_type';
   $this->logProcessActivity('Ticket Type Edited', $ticket_types,$log_name);
    $data = $this->validateTicketType($request, $ticket_types->id);
        $data += $request->only(['ticket_type','ticket_type_active']);
    $ticket_types->update($data);
    
return  response()->json(['success' => 'Ticket Type details updated successfully!']);
  }

public function destroy($id){
  $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
 if(!$cat_permission){ 
  return  response()->json(['message' => 'Not Authorized to see this page.'],200);
 }else{
  $user = Auth::user();
  $category = TicketType::find($id);
if ($category) {
$log_name='ticket_type';
   $this->logProcessActivity('Ticket Type Deleted', $category,$log_name);
    $category->delete();

    RoleCategoryPermission::where('category_id', $id)->delete();
}
  return response()->json(['message' => 'Record Deleted successfully!'],200);
  }
}
public function status_change($id,Request $request){

  $category=TicketType::find($id);
  if($request->status=='category_status_1'){
    $data['ticket_type_active']=0;
  }
  if($request->status=='category_status_0'){
    $data['ticket_type_active']=1;
  }
  $log_name='category';
   $this->logProcessActivity('Ticket Type Status Changed', $category,$log_name);
  $category->update($data);
  return response()->json(['message' => 'Status Updated successfully!'],200);
}
}
