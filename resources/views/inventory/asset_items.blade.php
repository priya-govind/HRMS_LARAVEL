@extends('layouts.app')
@section('content')
      
      <!-- partial:partials/_navbar.html -->
      @include('layouts.includes.topbar')
     
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('layouts.includes.sidebar')
           <meta name="csrf-token" content="{{ csrf_token() }}">
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
                        <div class="modal-content logic_cont">
                            <div class="modal-header">
                            <h5 class="modal-title" id="dataModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                                <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                                    <form id="AssetForm">
                                        @csrf
                                        
                                        <input type="hidden" name="recordId" id="recordId">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Item Type</label>
                                            <select name="item_type" id="item_type" class="form-control">
                                                <option value="">Select</option>
                                                <option value="asset">Asset</option>               
                                                <option value="accessory">Accessory</option>
                                                <option value="components">Components</option>
                                                <option value="licenses">Licenses</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Item Category</label>
                                            <select name="item_category" id="item_category" class="form-control">
                                                <option value="">Select</option>
                                                    @foreach($itemTypes as $itid =>$itval)
                                                        <option value="{{ $itid }}">{{ $itval }}</option>
                                                    @endforeach
                                                    <option value="others">Others</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Brand Type:</label>&nbsp;&nbsp;
                                                            <select name="item_brand" id="item_brand" class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach($BrandTypes as $id =>$val)
                                                                <option value="{{ $id }}">{{ $val }}</option>
                                                                @endforeach
                                                                 <option value="others">Others</option>
                                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Item Name</label>
                                            <input type="text" class="form-control" id="item_name"  name="item_name" placeholder="Item Name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="work_mode" class="form-label">Serial Number</label>
                                            <input type="text" name="serial_number" id="serial_number" placeholder="Serial Number" required>
                                        </div>
                                         <div class="mb-3">
                                            <label for="work_mode" class="form-label">Purchase Amount</label>
                                            <input type="text" name="purchased_amount" id="purchased_amount" placeholder="Purchased Amount" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="work_mode" class="form-label">Purchase Date</label>
                                            <input type="text" name="purchased_date" id="purchased_date" placeholder="Purchased Date" required>
                                        </div>
                                         <div class="mb-3" id="expiry_dt_col" style="display:none;">
                                            <label for="work_mode" class="form-label">Expiry Date</label>
                                            <input type="text" name="expiry_date" id="expiry_date" placeholder="Expiry Date" required>
                                            <span>Enter expiry date only if available its not mandatory
                                        </div>
                                         <div class="mb-3" id="edit_inventory" style="display: none;">
                                            <label for="work_mode" class="form-label">Asset Status</label>
                                           <select id="asset_status" name="asset_status" class="form-control">
                                            <option value="">Select</option>
                                            <option value="available" selected>Available</option>
                                            <option value="assigned">Assigned</option>
                                            <option value="damaged">Damaged</option>
                                            <option value="retired">Retired</option>
                                           </select>
                                        </div>
                                         <div class="mb-3" id="new_item" style="display:none;">
                                            <label for="start_date" class="form-label">Select New Item</label>
                                            <select name="new_assert" id="new_assert" class="form-control">
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
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
        $('#AssetsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('assets_manage.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'item_name', name: 'item_name' },
                    { data: 'item_type', name: 'item_type' },
                    { data: 'item_category', name: 'item_category' },
                    { data: 'item_brand', name: 'item_brand' },
                    { data: 'serial_number', name: 'serial_number' },
                    { data: 'status', name: 'status' },
                    { data: 'purchased_date', name: 'purchased_date' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 25,
            });
        });

    </script>
<!-- <script src="{{ asset('assets/js/common.js') }}"></script> -->
<script src="{{ asset('assets/js/assetitem.js') }}"></script>
@endsection