<?php

namespace App\Http\Controllers;
use App\Models\Inventory;
use App\Models\InventoryAssignment;
use App\Models\ItemType;
use App\Models\Brands;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\ActivityHelper;
use App\Helpers\PermissionHelper;


use Illuminate\Http\Request;

class InventoryController extends Controller
{
     public function index(Request $request) {
          if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        //$items = Inventory::with('AssetType')->get();
        $itemTypes=ItemType::where('item_status',config('global.active_status'))->pluck('item_type_name','id');
        $BrandTypes=Brands::where('brand_status',config('global.active_status'))->pluck('brand_name','id');
        $user_info=User::where('emp_status',config('global.active_status'))->where('support_access','!=',config('global.active_status'))->pluck('name','id');
        $LoadDatatables=true;
              if ($request->ajax()) {
                    $brand_types = Inventory::get();
                    return DataTables::of($brand_types)
                        ->addIndexColumn()
                        ->addColumn('asset_type', function($row) {      
                                return $row->AssetType->item_type_name;
                            })
                        ->addColumn('asset_brand', function($row) {      
                                return $row->AssetBrand->brand_name;
                            })
                            ->addColumn('asset_status', function($row) {      
                                return ucwords($row->asset_status);
                            })
                        ->addColumn('action', function($row) {
                            return '<button data-id="'.$row->id.'"  class="btn btn-primary assignInventory" title="Assign Inventory"> <i class="fas fa-box"></i> </button> &nbsp;|&nbsp;
                            <button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
                                <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i></button>
                            ';
                        })
                        ->rawColumns(['action'])
                        ->make(true);
                    }
        return view('inventory.index', compact('itemTypes','BrandTypes','LoadDatatables','user_info'));
    }
    public function store(Request $request){
    $validated = $request->validate([
        'asset_type' => 'required|exists:item_types,id',
        'asset_brand' => 'required|exists:brands,id',
        'asset_name' => 'required|string|max:255',
        'serial_number' => 'required|unique:inventories,serial_number',
    ]);

    $inventory = Inventory::create($validated);

    ActivityHelper::logActivity('Inventory item created', 'inventory', $inventory, [
        'request' => $request->all()
    ]);
    
    return  response()->json(['success' => 'Inventory item added successfully!']);
}
public function edit($id){
        $notify_typs= Inventory::find($id); 
        return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($notify_typs) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  

    }
public function update(Request $request, $id)
{
    $item = Inventory::findOrFail($id);

    $validated = $request->validate([
        'asset_type'    => 'required|exists:item_types,id',
        'asset_brand'   => 'required|exists:brands,id',
        'asset_name'    => 'required',
        'serial_number' => 'required|unique:inventories,serial_number,' . $item->id,
        'asset_status' => 'required',
    ]);

    $item->update($validated);
    if($request->asset_status=='damaged' && isset($request->new_assert) && !empty($request->new_assert)){
    //     $assignment = InventoryAssignment::where('inventory_id', $id)->first();
    //     echo $request->new_asset; die;
    //    // dd( $assignment);
    //     if ($assignment) {
    //         $assignment->inventory_id = $request->new_asset;
    //         $assignment->returned_at  = now();
    //         $assignment->save();
    //     }
        InventoryAssignment::where('inventory_id',$id)->update(['inventory_id' => $request->new_assert,'returned_at' =>now()]);
       // Inventory::where('id',$request->new_assert)->update('asset_status','assigned');
    }
    return response()->json(['success' => 'Inventory item updated successfully!']);
}
    public function destroy($id) {
      
       $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
        $item_type = Inventory::find($id);
            if ($item_type) {
            $log_name='Asset Deleted.';
            ActivityHelper::logActivity('Asset Deleted',$log_name, $item_type, [
                'request' => request()->all()
            ]);
            $item_type->delete();
            }
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }
    public function show($id){
        $notify_typs= Inventory::find($id); 
        $user=InventoryAssignment::where('inventory_id',$id)->pluck('employee_id');
        return response()->json(['data'=>$notify_typs,'user'=> $user]);  

    }
    public function store_assigned(Request $request){
       // Validate
    $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);
      $result=  InventoryAssignment::updateOrCreate(
            ['inventory_id' => $request->recordId], // search condition
            [
                'employee_id' => $request->user_id,
                'assigned_at' => now(),
            ]
        );
        if($result){
            Inventory::where('id',$request->recordId)->update(['asset_status' => 'assigned']);
        }
    // Return JSON for your JS
    return response()->json([
        'success' => 'Asset assigned successfully'
    ]);

    }
    // public function show_user_assets__($id){
    //    $query= Inventory::with('assignments');
    //       $query->whereHas('assignments.employee', function ($subQuery) use($id) {
    //                     $subQuery->where('id', $id);
    //        });
    //        $result=$query->get();
    //        foreach($result as $iniv){
    //             $inventory_name=$iniv->asset_name;
    //             $serial_no=$iniv->serial_number;
    //             echo  $inventory_name.'==>'.$serial_no.'<br/>';
    //        }
    // }
    public function show_user_assets($id) {
        $inventories = Inventory::with('assignments.employee')
            ->whereHas('assignments.employee', function ($subQuery) use ($id) {
                $subQuery->where('id', $id);
            })
            ->get();

        // Collect all inventory items
        $data = $inventories->map(function ($inv) {
            return [
                'item_name'   => $inv->asset_name,
                'serial_no'   => $inv->serial_number,
            ];
        });

        // Get user name from the first assignment (since all belong to same user)
        $userName = null;
        if ($inventories->isNotEmpty() && $inventories->first()->assignments->isNotEmpty()) {
            $userName = $inventories->first()->assignments->first()->employee->name;
        } else {
           $userName=User::where('id',$id)->pluck('name'); 
        }

        return response()->json([
            'success'   => true,
            'items'     => $data,
            'user_info' => $userName,
        ]);
    }
    public function replace_inventory($id){
        $damaged_inv_dtls = Inventory::findOrFail($id);
        if($damaged_inv_dtls->asset_status=='assigned'){
                $available_asset = Inventory::with(['AssetBrand:id,brand_name'])
                                            ->where('asset_status', 'available')
                                            ->where('asset_type', $damaged_inv_dtls->asset_type)
                                            ->get(['id', 'asset_name', 'asset_brand', 'serial_number']);

                    return response()->json([
                        'assigned' => true,
                        'available_items' => $available_asset->map(function($item) {
                            return [
                                'id' => $item->id,
                                'asset_name' => $item->asset_name,
                                'serial_number' => $item->serial_number,
                                'brand_name' => $item->AssetBrand->brand_name ?? null,
                            ];
                        })
                    ]);
        } else {
            return response()->json(['assigned' => false]);
        }

    }


}
