@extends('layouts.app')
@section('content')
      
      <!-- partial:partials/_navbar.html -->
      @include('layouts.includes.topbar')
     
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('layouts.includes.sidebar')
           <meta name="csrf-token" content="{{ csrf_token() }}">
           <style>
            #dataModal {
                padding-right: 20% !important;
            }
            .checkbox-container {
                width: 100%;
                height: 100px;
                border: 1px solid #ccc;
                padding: 10px;
                overflow-y: auto;
                background-color: #f9f9f9;
                border-radius: 5px;
            }
            </style>
        <!-- partial -->
        <div class="main-panel">
        <div class="content-wrapper">
            <div class="card">
                <div class="card-header">
                    <div class="float-start">
                         Inventory Items
                    </div>
                    <div class="float-end">
                         <button type="button" id="addButton" class="btn btn-primary btn-sm">+Add New Item
                    </div>
                <div class="card-body">
                <br/>
                    <div id="success-message" class="alert alert-success"  role="alert"  style="display: none;"></div>
                    <div id="error-message" class="alert alert-danger" style="display: none;"></div>


                     <form  id="assetReport">
                    @csrf
                    <div class="row form-row align-items-start">
                      <div class="form-group col-md-2">
                          <label for="teamType">Item Type</label>
                            <select name="item_type" id="search_item_type" class="form-control">
                                <option value="">Select</option>
                                <option value="asset">Asset</option>               
                                <option value="accessory">Accessory</option>
                                <option value="components">Components</option>
                                <option value="licenses">Licenses</option>
                            </select>
                      </div>
                       <div class="form-group col-md-2">
                          <label for="teamType">Item Category</label>
                            <select name="item_category" id="search_item_category" class="form-control">
                                <option value="">Select</option>
                                    @foreach($itemTypes as $itid =>$itval)
                                        <option value="{{ $itid }}">{{ $itval }}</option>
                                    @endforeach
                            </select>
                      </div>
                      <div class="form-group col-md-2">
                          <label for="teamType">Brand</label>
                            <select class="form-control" id="search_brand" name="brand">
                              <option value="">Select Brand</option>
                                @foreach ($BrandTypes as $id =>$brand)
                                  <option value="{{ $id  }}" {{ request('brand') == $id ? 'selected' : '' }}>{{ $brand }}</option>
                                @endforeach
                            </select>
                      </div>
                      <div class="form-group col-md-2">
                          <label for="teamType">Assigned Employees</label>
                            <select class="form-control" id="search_user_id" name="user_id">
                              <option value="">Select Employee</option>
                                @foreach ($user_info as $id =>$u_name)
                                  <option value="{{ $id  }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $u_name }}</option>
                                @endforeach
                            </select>
                      </div>
                      
                            <div class="form-group col-md-2" id="asset_configuration" style="display: none;">
                                <label for="teamType">Configuration:</label>
                                <div class="checkbox-container" id="search_configure">                                                           
                                </div>
                            </div>
                            <div class="form-group col-md-2" id="asset_config_features"  style="display: none;">
                                <label for="teamType">Features:</label>
                                <div class="checkbox-container" id="search_configure_attribute">                                                           
                                </div>
                            </div>
                       
                    </div>
                    <div class="row form-row align-items-start" style="margin-left: 37% !important;">
                      <div class="form-group col-md-2">
                         <button type="submit" class="btn btn-primary w-100">Filter</button>
                      </div>
                    </div>
  
                  </form>
                    <table id="AssetsTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sno </th>
                            <th>Item Name</th>
                            <th>Item Type</th>
                            <th>Item Category</th>
                            <th>Brand</th>
                            <th>Serial Number</th>
                            <th>Status</th>
                            <th>Purchased Date</th>
                            <th>Assigned Employee</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    </table> 
                </div>
                </div>
                </div>
                <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content logic_cont" style="width:150% !important;">
                            <div class="modal-header">
                            <h5 class="modal-title" id="dataModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                                <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                                    <form id="AssetForm" method="POST">
                                        <input type="hidden" name="recordId" id="recordId">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="name" class="form-label">Item Type</label>
                                                    <select name="item_type" id="item_type" class="form-control">
                                                        <option value="">Select</option>
                                                        <option value="asset">Asset</option>               
                                                        <option value="accessory">Accessory</option>
                                                        <option value="components">Components</option>
                                                        <option value="licenses">Licenses</option>
                                                    </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="name" class="form-label">Item Category</label>
                                                <select name="item_category" id="item_category" class="form-control">
                                                    <option value="">Select</option>
                                                        @foreach($itemTypes as $itid =>$itval)
                                                            <option value="{{ $itid }}">{{ $itval }}</option>
                                                        @endforeach
                                                        <option value="others">Others</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class=" row mb-3">
                                            <div class="col-md-6">
                                                <label for="name" class="form-label">Brand Type:</label>&nbsp;&nbsp;
                                                            <select name="item_brand" id="item_brand" class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach($BrandTypes as $id =>$val)
                                                                <option value="{{ $id }}">{{ $val }}</option>
                                                                @endforeach
                                                                 <option value="others">Others</option>
                                                            </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="start_date" class="form-label">Item Name</label>
                                                <input type="text" class="form-control" id="item_name"  name="item_name" placeholder="Item Name" required>
                                            </div>                                          
                                        </div>
                                        <div class=" row mb-3">
                                            <div class="col-md-6">
                                                 <label for="work_mode" class="form-label">Serial Number</label>
                                                 <input type="text" name="serial_number" id="serial_number" placeholder="Serial Number" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="work_mode" class="form-label">Purchase Amount</label>
                                                <input type="text" name="purchased_amount" id="purchased_amount" placeholder="Purchased Amount" required>
                                            </div>                              
                                        </div>
                                        <div class=" row mb-3">
                                            <div class="col-md-6">
                                                <label for="work_mode" class="form-label">Purchase Date</label>
                                            <input type="text" name="purchased_date" id="purchased_date" placeholder="Purchased Date" required>
                                            </div>
                                           <div class="col-md-6"  id="expiry_dt_col" style="display:none;">
                                            <label for="work_mode" class="form-label">Expiry Date</label>
                                            <input type="text" name="expiry_date" id="expiry_date" placeholder="Expiry Date" required>
                                            <span>Enter expiry date only if available its not mandatory</span>
                                            </div>
                                        </div>

                                        <div id="config_details_container" style="display:none;">
                                            <div id="config_container">

                                            </div>
                                        </div>
                                         <div class=" row mb-3">
                                            <div class="col-md-6"  id="edit_inventory" style="display: none;">
                                                 <label for="work_mode" class="form-label">Asset Status</label>
                                                    <select id="asset_status" name="asset_status" class="form-control">
                                                        <option value="">Select</option>
                                                        <option value="available" selected>Available</option>
                                                        <option value="assigned">Assigned</option>
                                                        <option value="damaged">Damaged</option>
                                                        <option value="retired">Retired</option>
                                                    </select>
                                            </div>
                                            <div class="col-md-6" id="new_item" style="display:none;">
                                                <label for="start_date" class="form-label">Select New Item</label>
                                                <select name="new_assert" id="new_assert" class="form-control">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                        </div>
                                         <div class="row mb-3">
                                            <div class="col-md-8">
                                                <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                                            </div>
                                        </div>
                                        
                                    </form>
                            </div>
                        </div>
                    </div>
                </div> 
                 <div class="modal fade" id="assignInventoryModal" tabindex="-1" aria-labelledby="assignInventoryModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content logic_cont">
                            <div class="modal-header">
                            <h5 class="modal-title" id="assignInventoryModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="success-message2" class="alert alert-success" role="alert" style="display: none;"></div>
                                <div id="error-message2" class="alert alert-danger" style="display: none;"></div>
                                    <form id="AssetAssignForm">
                                        @csrf
                                        
                                        <input type="hidden" name="recordId" id="recordId_assign">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Item Name</label>
                                            <input type="text" class="form-control" id="item_name_assign"  name="item_name" placeholder="Item Name" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="work_mode" class="form-label">Serial Number</label>
                                            <input type="text" name="serial_number" id="serial_number_assign" placeholder="Serial Number" readonly>
                                        </div>
                                         <div class="mb-3">
                                            <label for="work_mode" class="form-label">Assign to User</label>
                                             <select name="user_id" id="user_id" class="form-control">
                                                    <option value="">Select</option>
                                                     @foreach($user_info as $id =>$val)
                                                        <option value="{{ $id }}">{{ $val }}</option>
                                                     @endforeach
                                                               
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                                    </form>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="AddCategoryForm">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                            <h5 class="modal-title">Add New Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                            <input type="hidden" name="item_type" id="modal_item_type">
                            <div class="mb-3">
                                <label for="new_category" class="form-label">Category Name</label>
                                <input type="text" name="new_category" id="new_category" class="form-control" required>
                            </div>
                            </div>
                            <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Category</button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
                <div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="AddBrandForm">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                            <h5 class="modal-title">Add New Brand</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                            <div class="mb-3">
                                <label for="new_brand" class="form-label">Brand Name</label>
                                <input type="text" name="new_brand" id="new_brand" class="form-control" required>
                            </div>
                            </div>
                            <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Brand</button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
@include('layouts.includes.delete_popup')
<script type="text/javascript">
        $(document).ready(function () {
     const CategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'), {
                backdrop: 'static',
                keyboard: false
            });
    const BrandModal = new bootstrap.Modal(document.getElementById('addBrandModal'), {
                backdrop: 'static',
                keyboard: false
            });
        $('#AssetsTable').DataTable({
                processing: true,
                serverSide: true,
                  ajax: {
                        url: "{{ route('manage_items_configure_action') }}",
                        type: "POST",
                        data: function(d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.item_type = $('#search_item_type').val();
                        d.item_category = $('#search_item_category').val();
                        d.brand = $('#search_brand').val();
                        d.user_id = $('#search_user_id').val();
                       d.search_configure_attribute = $('input[name="search_configure_attribute[]"]:checked')
                                                        .map(function() { return $(this).val(); })
                                                        .get();

                        }
                    },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'item_name', name: 'item_name' },
                    { data: 'item_type', name: 'item_type' },
                    { data: 'item_category', name: 'item_category' },
                    { data: 'item_brand', name: 'item_brand' },
                    { data: 'serial_number', name: 'serial_number' },
                    { data: 'status', name: 'status' },
                    { data: 'purchased_date', name: 'purchased_date' },
                    { data: 'assigned_employee', name: 'assigned_employee' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 25,
            });
        });
$('#assetReport').on('submit', function(e) {
    e.preventDefault();
    $('#AssetsTable').DataTable().ajax.reload();
});

    </script>
<!-- <script src="{{ asset('assets/js/common.js') }}"></script> -->
<script src="{{ asset('assets/js/assetitem_configure.js') }}"></script>
@endsection