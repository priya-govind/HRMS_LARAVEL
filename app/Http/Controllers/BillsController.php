<?php

namespace App\Http\Controllers;


use App\Helpers\ActivityHelper;
use App\Helpers\PermissionHelper;
use App\Models\BillType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class BillsController extends Controller
{
public function index(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        if ($request->ajax()) {
        $bill_type = BillType::get();
        return DataTables::of($bill_type)
            ->addIndexColumn()
                ->addColumn('bill_typ_status', function($row) {
                    if($row->bill_typ_status==1){
                        $status='Active';
                    } else {
                        $status='InActive';
                    }
                return $status;
            })
            ->addColumn('action', function($row) {
                return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editbilltypeButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
                    <button type="button"  class="btn btn-danger btn-sm delete-billtype-btn" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i></button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
        }
           return view('bills.bill_type',['LoadDatatables' => true]);
}
public function store(Request $request) {
    
      $data = $request->validate([
          'bill_typ_name' => 'required|unique:bill_type',
          'bill_status' => 'required'
      ]);
      $data=$request->all();
      $data['bill_typ_name']=$request->input('bill_typ_name');
      $data['bill_typ_status']=$request->input('bill_status');

        // Mass assigment
        $bill_type = BillType::create($data);
        $log_name='bill_type';
        ActivityHelper::logActivity('New Bill Type created',$log_name, $bill_type, [
            'request' => request()->all()
        ]);      
      return  response()->json(['success' => 'Bill Type details Added successfully!']);
}
  public function edit($id){
        $notify_typs= BillType::find($id); 
        return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($notify_typs) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  

    }
public function update(Request $request){
    $bill_type=BillType::find($request->id);
    $log_name='bill_type';
    ActivityHelper::logActivity('Bill Type details Edited',$log_name, $bill_type, [
    'request' => request()->all()
    ]);
    $data = $request->validate([
        'bill_typ_name' => 'required|unique:bill_type,bill_typ_name,'.$request->id,
        'bill_status' => 'required'
    ]);
    $data=$request->all();
    $data['bill_typ_name']=$request->input('bill_typ_name');
    $data['bill_typ_status']=$request->input('bill_status');
    $bill_type->update($data);

    return  response()->json(['success' => 'Bill Type details updated successfully!','bill_type'=>$bill_type]);
}
    public function destroy($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
        $bill_type = BillType::find($id);
            if ($bill_type) {
            $log_name='bill_type';
            ActivityHelper::logActivity('Bill Type Deleted',$log_name, $bill_type, [
                'request' => request()->all()
            ]);
            $bill_type->delete();
            }
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }
}
