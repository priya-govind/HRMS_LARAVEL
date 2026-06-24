<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ExpenseItems;
use App\Helpers\ActivityHelper;
use App\Models\MonthlyExpense;
use App\Models\MonthlyExpenseItems;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
public function index(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        if ($request->ajax()) {
        $expense_items = ExpenseItems::where('id','!=', 9 )->get();
        return DataTables::of($expense_items)
            ->addIndexColumn()
                ->addColumn('expense_type_status', function($row) {
                    if($row->expense_type_status==1){
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
           return view('expenses.expense_items',['LoadDatatables' => true]);
}
  public function edit($id){
        $notify_typs= ExpenseItems::find($id); 
        return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($notify_typs) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  

    }
    public function store(Request $request){
   
   $data = $request->validate([
        'expense_type_name' => 'required|unique:expense_items,expense_type_name',
        'expense_type_status' => 'required'
    ]);
    $data=$request->all();
    $data['expense_type_name']=$request->input('expense_type_name');
    $data['expense_type_status']=$request->input('expense_type_status');
     $expense_items = ExpenseItems::create($data);
        $log_name='expense_items';
            ActivityHelper::logActivity('Bill Type Added Successfully',$log_name, $expense_items, [
            'request' => request()->all()
            ]);
    return  response()->json(['success' => 'Bill Type details Added successfully!','expense_items'=>$expense_items]);
}
public function update(Request $request){
    $expense_items=ExpenseItems::find($request->id);
    $log_name='expense_items';
    ActivityHelper::logActivity('Bill Type details Edited',$log_name, $expense_items, [
    'request' => request()->all()
    ]);
    $data = $request->validate([
        'expense_type_name' => 'required|unique:expense_items,expense_type_name,'.$request->id,
        'expense_type_status' => 'required'
    ]);
    $data=$request->all();
    $data['expense_type_name']=$request->input('expense_type_name');
    $data['expense_type_status']=$request->input('expense_type_status');
    $expense_items->update($data);

    return  response()->json(['success' => 'Bill Type details updated successfully!','expense_items'=>$expense_items]);
}
    public function destroy($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
        $expense_items = ExpenseItems::find($id);
            if ($expense_items) {
            $log_name='expense_items';
            ActivityHelper::logActivity('Bill Type Deleted',$log_name, $expense_items, [
                'request' => request()->all()
            ]);
            $expense_items->delete();
            }
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }
    // public function monthly_expenses(Request $request){
 
    // }

}