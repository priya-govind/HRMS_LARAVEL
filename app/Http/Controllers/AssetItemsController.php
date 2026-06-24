<?php

namespace App\Http\Controllers;
use App\Models\AssetTypes;
use App\Models\AccessoryTypes;
use App\Models\ComponentTypes;
use App\Models\SoftwareLicenses;
use App\Models\AssetItems;
use App\Models\AssignAsset;
use App\Models\Brands;
use App\Models\User;
use App\Models\AssetAttribute;
use App\Models\AssetAttributeOptions;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\ActivityHelper;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Carbon;

use Illuminate\Http\Request;

class AssetItemsController extends Controller
{
    public function index(Request $request) {
          if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $itemTypes=AssetTypes::where('asset_type_status',config('global.active_status'))->pluck('asset_type_name','id');
        $BrandTypes=Brands::where('brand_status',config('global.active_status'))->pluck('brand_name','id');
        $user_info=User::where('emp_status',config('global.active_status'))->where('support_access','!=',config('global.active_status'))->pluck('name','id');
        $LoadDatatables=true;
        $LoadDateTimepicker = true;
              if ($request->ajax()) {
                    $brand_types = AssetItems::get();
                    return DataTables::of($brand_types)
                        ->addIndexColumn()
                         ->addColumn('item_type', fn($row) => $row->item_type)
                        ->addColumn('item_category', function($row) {
                                                return $row->item_category_name; // uses the accessor defined above
                                            })
                        ->addColumn('item_brand', function($row) {      
                                                return $row->itemBrand->brand_name;
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
        return view('inventory.asset_items', compact('BrandTypes','itemTypes','user_info','LoadDatatables','LoadDateTimepicker'));
    }
    public function LoadAssetCategory($asset_category){
        if($asset_category=='asset'){
            $selectDropdown=AssetTypes::where('asset_type_status',config('global.active_status'))->get(['id','asset_type_name']);
                    return response()->json([
                        'load_data' => $selectDropdown->map(function($item) {
                            return [
                                'id' => $item->id,
                                'category_name' => $item->asset_type_name,
                            ];
                        })
                    ]);
        } 
         if($asset_category=='accessory'){
          $selectDropdown=AccessoryTypes::where('accessory_type_status',config('global.active_status'))->get(['id','accessory_type_name']);
                    return response()->json([
                        'load_data' => $selectDropdown->map(function($item) {
                            return [
                                'id' => $item->id,
                                'category_name' => $item->accessory_type_name,
                            ];
                        })
                    ]);  
        }  
        if($asset_category=='components'){
          $selectDropdown=ComponentTypes::where('component_type_status',config('global.active_status'))->get(['id','component_type_name']);
                    return response()->json([
                        'load_data' => $selectDropdown->map(function($item) {
                            return [
                                'id' => $item->id,
                                'category_name' => $item->component_type_name,
                            ];
                        })
                    ]);  
        }  
        if($asset_category=='licenses'){
            $selectDropdown=SoftwareLicenses::where('license_type_status',config('global.active_status'))->get(['id','license_type_name']);
                    return response()->json([
                        'load_data' => $selectDropdown->map(function($item) {
                            return [
                                'id' => $item->id,
                                'category_name' => $item->license_type_name,
                            ];
                        })
                    ]);
        }

    }
    public function LoadAssetBrands(){
         $selectDropdown=Brands::where('brand_status',config('global.active_status'))->get(['id','brand_name']);
                    return response()->json([
                        'load_data' => $selectDropdown->map(function($item) {
                            return [
                                'id' => $item->id,
                                'brand_name' => $item->brand_name,
                            ];
                        })
                    ]);

    }
    public function AddCategory(Request $request){
        $request->validate([
            'item_type' => 'required',
            'new_category' => 'required|string|max:255',
        ]);

        $newId = null;

        switch($request->item_type){
            case 'asset':
                $category = AssetTypes::updateOrCreate(
                    ['asset_type_name' =>$request->new_category ],
                    ['asset_type_status' => config('global.active_status')]
                );
                $newId = $category->id;
                break;

            case 'accessory':
                $category = AccessoryTypes::updateOrCreate(
                    ['accessory_type_name' =>$request->new_category ],
                    ['accessory_type_status' => config('global.active_status')]
                );
                $newId = $category->id;
                break;

            case 'components':
                $category = ComponentTypes::updateOrCreate(
                    ['component_type_name' =>$request->new_category ],
                    ['component_type_status' => config('global.active_status')]
                );
                $newId = $category->id;
                break;

            case 'licenses':
                $category = SoftwareLicenses::updateOrCreate(
                    ['license_type_name' =>$request->new_category ],
                    ['license_type_status' => config('global.active_status')]
                );
                $newId = $category->id;
                break;
        }
        return response()->json([
            'success' => true,
            'message' => 'Category added',
            'new_id' => $newId
        ]);
    }
    public function addBrand(Request $request){
        $request->validate([
            'new_brand' => 'required|string|max:255',
        ]);

        $newId = null;
        $brand = Brands::updateOrCreate(
                        ['brand_name' =>$request->new_brand ],
                        ['license_type_status' => config('global.active_status')]
                    );
                    $newId = $brand->id;

            return response()->json([
                'success' => true,
                'message' => 'Brand added',
                'new_id' => $newId
            ]);
    }
    public function store(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->add_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $validated = $request->validate([
            'item_type' => 'required',
            'item_category' => 'required',
            'item_brand' => 'required|exists:brands,id',
            'item_name' => 'required|string|max:255',
            'serial_number' => 'required|unique:inventories,serial_number',
            'purchased_amount' => 'nullable|numeric',
            'purchased_date'   => 'nullable|date',
            'expiry_date'      => 'nullable|date',

        ]);
           if ($request->purchased_date) {
            $validated['purchased_date'] = Carbon::createFromFormat('d-m-Y', $request->purchased_date)
                                                ->format('Y-m-d');
        }

        if ($request->expiry_date) {
            $validated['expiry_date'] = Carbon::createFromFormat('d-m-Y', $request->expiry_date)
                                            ->format('Y-m-d');
        }

        $inventory = AssetItems::create($validated);

        ActivityHelper::logActivity('New item created', 'asset', $inventory, [
            'request' => $request->all()
        ]);
        
        return  response()->json(['success' => 'New item added successfully!']);
    }
    public function edit($id){
        $notify_typs= AssetItems::find($id); 
        return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($notify_typs) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    }
    public function update(Request $request, $id)
    {
         if (!PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $item = AssetItems::findOrFail($id);

        $validated = $request->validate([
            'item_type'        => 'required',
            'item_category'    => 'required',
            'item_brand'       => 'required|exists:brands,id',
            'item_name'        => 'required|string|max:255',
            'serial_number'    => 'required|unique:asset_items,serial_number,' . $item->id,
            'purchased_amount' => 'nullable|numeric',
            'purchased_date'   => 'nullable|date_format:d-m-Y',
            'expiry_date'      => 'nullable|date_format:d-m-Y',
        ]);

        if ($request->purchased_date) {
            $validated['purchased_date'] = Carbon::createFromFormat('d-m-Y', $request->purchased_date)
                                                ->format('Y-m-d');
        }

        if ($request->expiry_date) {
            $validated['expiry_date'] = Carbon::createFromFormat('d-m-Y', $request->expiry_date)
                                            ->format('Y-m-d');
        }

        $item->update($validated);

        if ($request->asset_status == 'damaged' && !empty($request->new_assert)) {
            AssetItems::where('id', $request->new_assert)
                    ->update(['status' => 'assigned']);
            AssetItems::where('id', $id)
                    ->update(['status' => 'damaged']);

        }

        return response()->json(['success' => 'Inventory item updated successfully!']);
    }
    public function show($id){
        $notify_typs= AssetItems::find($id); 
        $user=AssignAsset::where('asset_item_id',$id)->pluck('employee_id');
        return response()->json(['data'=>$notify_typs,'user'=> $user]);  

    }
    public function store_assigned(Request $request){
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
            $result=  AssignAsset::updateOrCreate(
                    ['asset_item_id' => $request->recordId], // search condition
                    [
                        'employee_id' => $request->user_id,
                        'assigned_at' => now(),
                    ]
                );
                if($result){
                    AssetItems::where('id',$request->recordId)->update(['status' => 'assigned']);
                }
            // Return JSON for your JS
            return response()->json([
                'success' => 'Asset assigned successfully'
            ]);
    }
    public function replace_inventory($id){
        $damaged_inv_dtls = AssetItems::findOrFail($id);
        if($damaged_inv_dtls->status=='assigned'){
                $available_asset = AssetItems::with(['itemBrand:id,brand_name'])
                                            ->where('status', 'available')
                                            ->where('item_type', $damaged_inv_dtls->item_type)
                                            ->get(['id', 'item_name', 'item_brand', 'serial_number']);

                    return response()->json([
                        'assigned' => true,
                        'available_items' => $available_asset->map(function($item) {
                            return [
                                'id' => $item->id,
                                'item_name' => $item->item_name,
                                'serial_number' => $item->serial_number,
                                'brand_name' => $item->itemBrand->brand_name ?? null,
                            ];
                        })
                    ]);
        } else {
            return response()->json(['assigned' => false]);
        }

    }
    public function check_assigned($id){
        $asset_dtls = AssetItems::findOrFail($id);
        $current_status=$asset_dtls->status;
        if($current_status!='assigned'){
            return response()->json([
                        'proceed' => true,  
                        'message' => 'deleting...'
                         ]);  
        } else {
           return response()->json([
                        'proceed' => false,  
                        'message' => 'User is linked with this Item. Assign user  to other available item.'
                         ]);   
        }
    }
    public function destroy($id) {
      
       $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
        $item_type = AssetItems::find($id);
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
    
   
       
}
