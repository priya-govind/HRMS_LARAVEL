<?php

namespace App\Http\Controllers;
use App\Helpers\PermissionHelper;
use App\Helpers\ActivityHelper;
use Yajra\DataTables\Facades\DataTables;
use App\Models\SoftwareLicenses;
use Illuminate\Http\Request;

class SoftwareLicensesController extends Controller
{
    public function index(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        if ($request->ajax()) {
        $item_type = SoftwareLicenses::get();
        $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
        $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);
        return DataTables::of($item_type)
            ->addIndexColumn()
                ->addColumn('license_type_status', function($row) {
                    if($row->license_type_status==1){
                        $status='Active';
                    } else {
                        $status='InActive';
                    }
                return $status;
            })
            ->addColumn('action', function($row)  use($edit_permit,$delete_permit) {
                $editButton =$edit_permit ? '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>' : '';
                $deleteButton = $delete_permit ?  '&nbsp;|&nbsp;<button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i></button>' : '';
                return $editButton. $deleteButton;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('inventory.software_licenses',['LoadDatatables' => true]);
    }
        public function store(Request $request) {
      $data = $request->validate([
          'license_type_name' => 'required|unique:licenses_types',
          'license_type_status' => 'required'
      ]);
      $data=$request->all();
      $data['license_type_name']=$request->input('license_type_name');
      $data['license_type_status']=$request->input('license_type_status');
        // Mass assigment
        $item_type = SoftwareLicenses::create($data);
        $log_name='licenses_types';
        ActivityHelper::logActivity('License Type Name created',$log_name, $item_type, [
            'request' => request()->all()
        ]);      
      return  response()->json(['success' => 'License Type Name details Added successfully!']);
    }
     public function edit($id){
          if (!PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $notify_typs= SoftwareLicenses::find($id); 
        return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($notify_typs) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    }
    public function update(Request $request){
        $item_type=SoftwareLicenses::find($request->id);
        $log_name='licenses_types';
        ActivityHelper::logActivity('License Type Name Edited',$log_name, $item_type, [
        'request' => request()->all()
        ]);
        $data = $request->validate([
          'license_type_name' => 'required|unique:licenses_types,license_type_name,'.$request->id,
          'license_type_status' => 'required'
        ]);
        $data=$request->all();
        $data['license_type_name']=$request->input('license_type_name');
        $data['license_type_status']=$request->input('license_type_status');
        $item_type->update($data);
        return  response()->json(['success' => 'License Type Name details updated successfully!','item_type'=>$item_type]);
    }
    public function destroy($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
        $item_type = SoftwareLicenses::find($id);
            if ($item_type) {
            $log_name='licenses_types';
            ActivityHelper::logActivity('License Type Name Deleted',$log_name, $item_type, [
                'request' => request()->all()
            ]);
            $item_type->delete();
            }
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }

}
