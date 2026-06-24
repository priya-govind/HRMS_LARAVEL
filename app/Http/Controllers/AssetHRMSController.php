<?php

namespace App\Http\Controllers;
use App\Models\AssetAttribute;
use App\Models\AssetAttributeOptions;
use App\Models\AssetTypes;
use App\Models\Brands;
use App\Models\ItemAttributeRelation;
use App\Models\User;
use App\Models\AssetItems;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\ActivityHelper;
use App\Helpers\PermissionHelper;
use App\Models\AssetConfiguration;
use Illuminate\Support\Carbon;



use Illuminate\Http\Request;

class AssetHRMSController extends Controller
{
    /**Attributes For Assets  Starts*/
    public function asset_attribute(Request $request){
         if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $LoadDatatables=true;
              if ($request->ajax()) {
                    $brand_types = AssetAttribute::get();
                    return DataTables::of($brand_types)
                        ->addIndexColumn()
                        ->addColumn('attribute_status',function($row){
                            if($row->attribute_status==1) {
                                $status='<button class="btn btn-success btn-sm"> Active</button>';
                            }  else  {
                                $status='  <button class="btn btn-danger btn-sm">In Active</button>';
                            }
                                return $status;
                        })
                        ->addColumn('action', function($row) {
                            return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
                                <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i></button>
                            ';
                        })
                        ->rawColumns(['attribute_status','action'])
                        ->make(true);
                    }
         return view('inventory.asset_attributes', compact('LoadDatatables'));
    }
    public function add_attribute(Request $request){
         if (!PermissionHelper::checkPermission('global.categories', $this->add_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $request->validate([
            'attribute_name' => 'required|string|max:255',
            'attribute_status' => 'required',
            'attribute_options' => 'required|array',
        ]);

        $newId = null;
        $attr_id = AssetAttribute::updateOrCreate(
                        ['attribute_name' =>$request->attribute_name],
                        ['attribute_status' => $request->attribute_status]
                    );
                    $newId = $attr_id->id;
                foreach($request->attribute_options as $option){
                    $option_exist = AssetAttributeOptions::where('attribute_id', $attr_id->id)
                                                ->where('attribute_options', $option)
                                                ->first();
                    if(!$option_exist){
                        AssetAttributeOptions::create([
                            'attribute_id' => $attr_id->id,
                            'attribute_options' => $option,
                        ]);
                    }
                }
        return response()->json([
            'success' =>  'Attribute added',
            'new_id' => $newId
        ]);
    }
    public function edit_attribute($id){
        $notify_typs= AssetAttribute::find($id);
        $options=AssetAttributeOptions:: where('attribute_id',$id)->get();
        return response()->json(['items' =>$notify_typs,'options' => $options ]);
    }
    public function update_attribute(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
                return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
            }
        $item = AssetAttribute::findOrFail($request->id);

        $request->validate([
            'attribute_name'   => 'required|string|max:255|unique:attribute,attribute_name,' . $item->id,
            'attribute_status' => 'required',
            'attribute_options'=> 'required|array',
        ]);

        // Update the main attribute
        $item->update([
            'attribute_name'   => $request->attribute_name,
            'attribute_status' => $request->attribute_status,
        ]);

        // Sync options
        foreach ($request->attribute_options as $option) {
            AssetAttributeOptions::updateOrCreate(
                ['attribute_id' => $item->id, 'attribute_options' => $option],
                ['attribute_options' => $option]
            );
        }

        return response()->json([
            'success' => 'Attribute updated',
            'new_id'  => $item->id,
        ]);
    }
    public function attribute_destroy($id) {
       $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
            $item_type = AssetAttribute::find($id);
            AssetAttributeOptions::where('attribute_id',$id)->delete();
                if ($item_type) {
                $log_name='Attribute Name and options Deleted.';
                ActivityHelper::logActivity('Attribute and its options Deleted',$log_name, $item_type, [
                    'request' => request()->all()
                ]);
                $item_type->delete();
                }
            return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }
    public function delete_attr_option($opt_id){
        $item_type = AssetAttributeOptions::find($opt_id)->delete();
                if ($item_type) {
                $log_name='Attribute Option Deleted.';
                ActivityHelper::logActivity('Attribute option Deleted',$log_name, $item_type, [
                    'request' => request()->all()
                ]);
                }
            return response()->json(['message' => 'Record Deleted successfully!'],200);
    }
/**Attributes For Assets Ends*/
/**Assets Types For HRMS Starts */
    public function manage_assets_types(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $attributes=AssetAttribute::where('attribute_status',config('global.active_status'))->get();
        if ($request->ajax()) {
        $item_type = AssetTypes::get();

        $edit_permit = PermissionHelper::checkPermission('global.categories', $this->edit_perm);
        $delete_permit = PermissionHelper::checkPermission('global.categories', $this->del_perm);
        return DataTables::of($item_type)
            ->addIndexColumn()
                ->addColumn('asset_type_status', function($row) {
                    if($row->asset_type_status==1){
                        $status='Active';
                    } else {
                        $status='InActive';
                    }
                return $status;
            })
            ->addColumn('action', function($row) use($edit_permit,$delete_permit) {
                 $editButton =$edit_permit ? '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>' : '';
                 $deleteButton = $delete_permit ?  '&nbsp;|&nbsp;<button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i></button>' : '';
                return $editButton. $deleteButton;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        $LoadDatatables=true;
        return view('inventory.manage_asset_types',compact('LoadDatatables','attributes'));
    }
    public function store_assets_types(Request $request) {
      $newId = null;
      $data = $request->validate([
          'asset_type_name' => 'required|unique:asset_types',
          'asset_type_status' => 'required',
          'configure' => 'required|array',

      ]);
      $item_id = AssetTypes::updateOrCreate(
                        ['asset_type_name' => $request->asset_type_name],
                        ['asset_type_status' => $request->asset_type_status]
                    );
              $newId = $item_id->id;
               foreach($request->configure as $option){
                    $option_exist = AssetConfiguration::where('asset_id', $newId)
                                                ->where('attribute_id', $option)
                                                ->first();
                    if(!$option_exist){
                        AssetConfiguration::create([
                            'asset_id' => $newId,
                            'attribute_id' => $option,
                        ]);
                    }
                }
        $log_name='asset_types';
        ActivityHelper::logActivity('Asset Type Name and Cofiguration Added.',$log_name, $item_id, [
            'request' => request()->all()
        ]);
      return  response()->json(['success' => 'Asset Type Name details Added successfully!']);
    }
     public function edit_assets_types($id){
         if (!PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $notify_typs= AssetTypes::find($id);
        $asset_config=AssetConfiguration::where('asset_id',$id) ->pluck('attribute_id')->toArray();
        return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json(['asset_type_info'=> $notify_typs, 'asset_config'=> $asset_config]) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
    }
    public function update_assets_types(Request $request){
        $item_type=AssetTypes::find($request->id);

        $data = $request->validate([
          'asset_type_name' => 'required|unique:asset_types,asset_type_name,'.$request->id,
          'asset_type_status' => 'required',
          'configure' => 'required|array',
        ]);
        $data=$request->all();
        $data['asset_type_name']=$request->input('asset_type_name');
        $data['asset_type_status']=$request->input('asset_type_status');
        $item_type->update($data);
                          $newId = $request->id;
                          if(!empty($request->configure)){
                            $delete=AssetConfiguration::where('asset_id', $newId)->delete();
                                foreach($request->configure as $option){
                                    $option_exist = AssetConfiguration::where('asset_id', $newId)
                                                                ->where('attribute_id', $option)
                                                                ->first();
                                    if(!$option_exist){
                                        AssetConfiguration::create([
                                            'asset_id' => $newId,
                                            'attribute_id' => $option,
                                        ]);
                                    }
                                }
                          }
                         
                          $log_name='asset_types';
                            ActivityHelper::logActivity('Asset Type Name and Configuration Details Edited',$log_name, $item_type, [
                            'request' => request()->all()
                            ]);
        return  response()->json(['success' => 'Asset Type Name details updated successfully!','item_type'=>$item_type]);
    }
    public function destroy_assets_types($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
        $item_type = AssetTypes::find($id);
            if ($item_type) {
            $log_name='asset_types';
            ActivityHelper::logActivity('Asset Type Name and configuration details Deleted',$log_name, $item_type, [
                'request' => request()->all()
            ]);
            $asset_config=AssetConfiguration::where('asset_id',$id)->delete();
            $item_type->delete();
            }
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }
  /**Assets Types For HRMS Ends */
  /**Items For HRMS starts */
    public function index(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $itemTypes=AssetTypes::where('asset_type_status',config('global.active_status'))->pluck('asset_type_name','id');
        $BrandTypes=Brands::where('brand_status',config('global.active_status'))->pluck('brand_name','id');
        $user_info=User::where('emp_status',config('global.active_status'))->where('support_access','!=',config('global.active_status'))->pluck('name','id');
        $LoadDatatables=true;
        $LoadDateTimepicker = true;
        return view('inventory.asset_items_configure', compact('BrandTypes','itemTypes','user_info','LoadDatatables','LoadDateTimepicker'));
    }
    public function manage_items_configure_action(Request $request){
            $asset_type = $request->input('item_type');
            $asset_category = $request->input('item_category');
            $brand = $request->input('brand');
            $user_id = $request->input('user_id');
            $attrIds = $request->input('search_configure_attribute', []); 

            // Convert to Y-m-d format
             $main_query = AssetItems::with('assignments','ItemConfigurationValues');

            if ($asset_type) {
                $main_query->where('item_type', $asset_type);
            }
            if($asset_category){
                $main_query->where('item_category', $asset_category);
            }
            if ($brand) {
                $main_query->where('item_brand', $brand);
            }
            if ($user_id) {
                $main_query->whereHas('assignments.employee', function ($subQuery) use ($user_id) {
                        $subQuery->whereIn('id', $user_id);
                });
            }
            if($attrIds){
                $main_query->whereHas('ItemConfigurationValues', function ($subQuery) use ($attrIds) {
                        $subQuery->whereIn('option_id', $attrIds);
                    });
            }


            $tickets = $main_query->get();
                         // This will print the full SQL with values
        //     $sql = vsprintf(
        //         str_replace('?', "'%s'", $tickets->toSql()),
        //         $tasks->getBindings()
        //     );
        //   echo $sql;
        //   die;
            return DataTables::of($tickets)
                ->addIndexColumn()
                ->addColumn('item_type', fn($row) => $row->item_type)
                ->addColumn('item_category', function($row) {
                                        return $row->item_category_name; // uses the accessor defined above
                                    })
                ->addColumn('item_brand', function($row) {      
                                        return $row->itemBrand->brand_name;
                                    })
                ->addColumn('inventory_name', fn($row) => $row->asset_name)
                //->addColumn('assigned_employee', fn($row) => $row->assignments?? 'N/A')
                ->addColumn('assigned_employee', function($row) {
                        if ($row->assignments->isNotEmpty()) {
                            // Assuming each assignment has an `employee` relation with `name`
                            return $row->assignments->map(fn($a) => $a->employee->name)->implode(', ');
                        }
                        return '-';
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
        $inventory = AssetItems::updateOrCreate($validated);

        ActivityHelper::logActivity('New item created', 'asset', $inventory, [
            'request' => $request->all()
        ]);

       $selectedAttributes = $request->input('attributes');
            if(!empty($selectedAttributes) && !empty($inventory->id)){
                $existing=ItemAttributeRelation::where('item_id',$inventory->id)->delete();
                foreach ($selectedAttributes as $attributeId => $optionId) {
                        ItemAttributeRelation::create([
                            'item_id'      =>$inventory->id,
                            'attribute_id' => $attributeId,
                            'option_id'    => $optionId,
                        ]);
                    }
            }
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

             $selectedAttributes = $request->input('attributes');
            if(!empty($selectedAttributes) && !empty($id) && $request->item_type=='asset'){
                $existing=ItemAttributeRelation::where('item_id',$id)->delete();
                foreach ($selectedAttributes as $attributeId => $optionId) {
                        ItemAttributeRelation::create([
                            'item_id'      =>$id,
                            'attribute_id' => $attributeId,
                            'option_id'    => $optionId,
                        ]);
                    }
            }
            if($request->item_type!='asset'){
                $existing1=ItemAttributeRelation::where('item_id',$id); 
                     $log_name='Asset Deleted.';
                    ActivityHelper::logActivity('Asset Configuration Deleted as Asset type changed',$log_name, $existing1, [
                        'request' => request()->all()
                    ]);  
                   $existing1 ->delete();
            }
                    if ($request->asset_status == 'damaged' && !empty($request->new_assert)) {
                        AssetItems::where('id', $request->new_assert)
                                ->update(['status' => 'assigned']);
                        AssetItems::where('id', $id)
                                ->update(['status' => 'damaged']);

                    }else  if ($request->asset_status == 'damaged' && empty($request->new_assert)) {
                        return response()->json(['failure' => 'No Item Available to Assign!!!']);
                    } else {
                        return response()->json(['success' => 'Inventory item updated successfully!!!!']);
                    }
       // return response()->json(['success' => 'Inventory item updated successfully!']);
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
    public function show($assetId, $Itemid = ''){
        $asset = AssetTypes::with('configurations.attribute.options')
            ->findOrFail($assetId);

        // Always initialize
        $item_options = [];

        if (!empty($Itemid)) {
            $item_options = ItemAttributeRelation::where('item_id', $Itemid)
                ->pluck('option_id')
                ->toArray();
        }

        // Return JSON for JS
        return response()->json(
            $asset->configurations->map(function ($config) use ($item_options) {
                return [
                    'attribute_id'   => $config->attribute->id,
                    'attribute_name' => $config->attribute->attribute_name,
                    'options'        => $config->attribute->options->map(function ($opt) use ($item_options) {
                        $selected_val = in_array($opt->id, $item_options) ? true : false;
                        return [
                            'option_id'   => $opt->id,
                            'option_name' => $opt->attribute_options,
                            'is_selected' => $selected_val,
                        ];
                    }),
                ];
            })
        );
    }
    public function manage_configuration($Itemid){
            $selectDropdown = AssetTypes::with('configurations.attribute')->findOrFail($Itemid);

            return response()->json([
                'load_data' => $selectDropdown->configurations->map(function($config) {
                    return [
                        'attribute_id'   => $config->attribute->id,
                        'attribute_name' => $config->attribute->attribute_name,
                    ];
                })
            ]);

    }
    public function manage_config_feature(Request $request){
        $configIds = $request->input('config_ids', []); // array of IDs

        $selectDropdown = AssetAttributeOptions::whereIn('attribute_id', $configIds)->get();

        return response()->json([
            'load_data' => $selectDropdown->map(function($config) {
                return [
                    'option_id'   => $config->id, // or $config->attribute_id if that's the correct column
                    'option_name' => $config->attribute_options,
                ];
            })
        ]);
    }
     public function damaged_items(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $itemTypes=AssetTypes::where('asset_type_status',config('global.active_status'))->pluck('asset_type_name','id');
        $BrandTypes=Brands::where('brand_status',config('global.active_status'))->pluck('brand_name','id');
        $user_info=User::where('emp_status',config('global.active_status'))->where('support_access','!=',config('global.active_status'))->pluck('name','id');
        $LoadDatatables=true;
        $LoadDateTimepicker = true;
        return view('inventory.damaged_items', compact('BrandTypes','itemTypes','user_info','LoadDatatables','LoadDateTimepicker'));
    }
    public function damaged_items_action(Request $request){
            $asset_type = $request->input('item_type');
            $asset_category = $request->input('item_category');
            $brand = $request->input('brand');
            $user_id = $request->input('user_id');
            $attrIds = $request->input('search_configure_attribute', []); 

            // Convert to Y-m-d format
             $main_query = AssetItems::with('assignments','ItemConfigurationValues')->where('status','damaged');

            if ($asset_type) {
                $main_query->where('item_type', $asset_type);
            }
            if($asset_category){
                $main_query->where('item_category', $asset_category);
            }
            if ($brand) {
                $main_query->where('item_brand', $brand);
            }
            if ($user_id) {
                $main_query->whereHas('assignments.employee', function ($subQuery) use ($user_id) {
                        $subQuery->whereIn('id', $user_id);
                });
            }
            if($attrIds){
                $main_query->whereHas('ItemConfigurationValues', function ($subQuery) use ($attrIds) {
                        $subQuery->whereIn('option_id', $attrIds);
                    });
            }


            $tickets = $main_query->get();
            return DataTables::of($tickets)
                ->addIndexColumn()
                ->addColumn('item_type', fn($row) => $row->item_type)
                ->addColumn('item_category', function($row) {
                                        return $row->item_category_name; // uses the accessor defined above
                                    })
                ->addColumn('item_brand', function($row) {      
                                        return $row->itemBrand->brand_name;
                                    })
                ->addColumn('inventory_name', fn($row) => $row->asset_name)
                //->addColumn('assigned_employee', fn($row) => $row->assignments?? 'N/A')
                ->addColumn('assigned_employee', function($row) {
                        if ($row->assignments->isNotEmpty()) {
                            // Assuming each assignment has an `employee` relation with `name`
                            return $row->assignments->map(fn($a) => $a->employee->name)->implode(', ');
                        }
                        return '-';
                    })
                ->make(true);

    }
 /**Items For HRMS ends */
}
