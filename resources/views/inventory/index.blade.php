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
                         <button type="button" id="addButton" class="btn btn-primary btn-sm">+Add New Inventory
                    </div>
                <div class="card-body">
                <br/>
                    <div id="success-message" class="alert alert-success"  role="alert"  style="display: none;"></div>
                    <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                    <table id="AssetsTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sno </th>
                            <th>Inventory Name</th>
                            <th>Inventory Type</th>
                            <th>Brand</th>
                            <th>Serial Number</th>
                            <th>Status</th>
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
                                    <form id="InventoryForm">
                                        @csrf
                                        
                                        <input type="hidden" name="recordId" id="recordId">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Item Type</label>
                                            <select name="asset_type" id="asset_type" class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach($itemTypes as $id =>$val)
                                                                <option value="{{ $id }}">{{ $val }}</option>
                                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Brand Type:</label>&nbsp;&nbsp;
                                                            <select name="asset_brand" id="asset_brand" class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach($BrandTypes as $id =>$val)
                                                                <option value="{{ $id }}">{{ $val }}</option>
                                                                @endforeach
                                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Item Name</label>
                                            <input type="text" class="form-control" id="asset_name"  name="asset_name" placeholder="Item Name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="work_mode" class="form-label">Serial Number</label>
                                            <input type="text" name="serial_number" id="serial_number" placeholder="Serial Number" required>
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
                                            <input type="text" class="form-control" id="asset_name_assign"  name="asset_name" placeholder="Item Name" readonly>
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
            </div>
@include('layouts.includes.delete_popup')
<script type="text/javascript">
        $(document).ready(function () {
        $('#AssetsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('manage_inventory.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'asset_name', name: 'asset_name' },
                    { data: 'asset_type', name: 'asset_type' },
                    { data: 'asset_brand', name: 'asset_brand' },
                     { data: 'serial_number', name: 'serial_number' },
                     { data: 'asset_status', name: 'asset_status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 25,
            });
        });
    </script>
<!-- <script src="{{ asset('assets/js/common.js') }}"></script> -->
<script src="{{ asset('assets/js/inventory.js') }}"></script>
@endsection