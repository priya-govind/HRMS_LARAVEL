<?php

namespace App\Http\Controllers;
use App\Helpers\PermissionHelper;
use App\Helpers\ActivityHelper;
use Yajra\DataTables\Facades\DataTables;

use App\Models\ItemType;
use App\Models\Brands;

use Illuminate\Http\Request;

class ItemTypeController extends Controller
{
     public function index(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        if ($request->ajax()) {
        $item_type = ItemType::get();
        return DataTables::of($item_type)
            ->addIndexColumn()
                ->addColumn('item_status', function($row) {
                    if($row->item_status==1){
                        $status='Active';
                    } else {
                        $status='InActive';
                    }
                return $status;
            })
            ->addColumn('action', function($row) {
                return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
                    <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i></button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('inventory.item_types',['LoadDatatables' => true]);
    }
        public function store(Request $request) {
      $data = $request->validate([
          'item_type_name' => 'required|unique:item_types',
          'item_status' => 'required'
      ]);
      $data=$request->all();
      $data['item_type_name']=$request->input('item_type_name');
      $data['item_status']=$request->input('item_status');

        // Mass assigment
        $item_type = ItemType::create($data);
        $log_name='item_types';
        ActivityHelper::logActivity('Item Type Name created',$log_name, $item_type, [
            'request' => request()->all()
        ]);      
      return  response()->json(['success' => 'Item Type Name details Added successfully!']);
    }
     public function edit($id){
        $notify_typs= ItemType::find($id); 
        return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($notify_typs) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  

    }
    public function update(Request $request){
        $item_type=ItemType::find($request->id);
        $log_name='item_types';
        ActivityHelper::logActivity('Item Type Name Edited',$log_name, $item_type, [
        'request' => request()->all()
        ]);
        $data = $request->validate([
          'item_type_name' => 'required|unique:item_types,item_type_name,'.$request->id,
          'item_status' => 'required'
        ]);
        $data=$request->all();
        $data['item_type_name']=$request->input('item_type_name');
        $data['item_status']=$request->input('item_status');
        $item_type->update($data);

        return  response()->json(['success' => 'Item Type Name details updated successfully!','item_type'=>$item_type]);
    }

    public function destroy($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
        $item_type = ItemType::find($id);
            if ($item_type) {
            $log_name='item_types';
            ActivityHelper::logActivity('Item Type Name Deleted',$log_name, $item_type, [
                'request' => request()->all()
            ]);
            $item_type->delete();
            }
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }
    public function brands_list(Request $request){
        // if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
        //     // Redirect if permission is denied
        //     return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        // }
        if ($request->ajax()) {
        $brand_types = Brands::get();
        return DataTables::of($brand_types)
            ->addIndexColumn()
                ->addColumn('brand_status', function($row) {
                    if($row->brand_status==1){
                        $status='Active';
                    } else {
                        $status='InActive';
                    }
                return $status;
            })
            ->addColumn('action', function($row) {
                return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
                    <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i></button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('inventory.brand_types',['LoadDatatables' => true]);
    }
    public function store_brand(Request $request) {
        $data = $request->validate([
            'brand_name' => 'required|unique:brands',
            'brand_status' => 'required'
        ]);
        $data=$request->all();
        $data['brand_name']=$request->input('brand_name');
        $data['brand_status']=$request->input('brand_status');

            // Mass assigment
            $item_type = Brands::create($data);
            $log_name='brands';
            ActivityHelper::logActivity('Brand Name created',$log_name, $item_type, [
                'request' => request()->all()
            ]);      
        return  response()->json(['success' => 'Brand Name details Added successfully!']);
    }
    public function edit_brand($id){
        $notify_typs= Brands::find($id); 
        return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($notify_typs) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  

    }
    public function update_brand(Request $request){
        $item_type=Brands::find($request->id);
        $log_name='brands';
        ActivityHelper::logActivity('Brand Name Edited',$log_name, $item_type, [
        'request' => request()->all()
        ]);
        $data = $request->validate([
          'brand_name' => 'required|unique:brands,brand_name,'.$request->id,
          'brand_status' => 'required'
        ]);
        $data=$request->all();
        $data['brand_name']=$request->input('brand_name');
        $data['brand_status']=$request->input('brand_status');
        $item_type->update($data);

        return  response()->json(['success' => 'Brand Name details updated successfully!','item_type'=>$item_type]);
    }

    public function destroy_brand($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
        $item_type = Brands::find($id);
            if ($item_type) {
            $log_name='brands';
            ActivityHelper::logActivity('Brand Name Deleted',$log_name, $item_type, [
                'request' => request()->all()
            ]);
            $item_type->delete();
            }
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }

}
