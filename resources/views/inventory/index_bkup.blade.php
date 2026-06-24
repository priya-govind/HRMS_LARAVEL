@extends('layouts.app')
@section('content')
      
      <!-- partial:partials/_navbar.html -->
      @include('layouts.includes.topbar')
     
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('layouts.includes.sidebar')
        
        <!-- partial -->
        <div class="main-panel">
        <div class="content-wrapper">
                     <div class="card">
            <div class="card-header">
                <div class="float-start">
                    Inventory Items
                </div>
                    <div class="card-body">
                         <br/>
                         <div id="success-message" class="alert alert-success"  role="alert"  style="display: none;"></div>
                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                            <div class="mb-4 pb-3"> 
                                <h6 id="dataLabel" class="mb-3"> Add New Inventory Item</h6>
                                {{-- <pre>{{ print_r(request()->all(), true) }}</pre> --}}
                                <form name="InventoryForm" id="InventoryForm">
                                    @csrf
                                    <input type="hidden" name="recordId" id="recordId">
                                    <div class="card" style="padding:40px;">
                                    <div class="row g-3">
                                            <div class="col-md-2">
                                                <label for="end_date" class="form-label">Item Type</label>
                                                <select name="asset_type" id="asset_type" class="form-control">
                                                    <option value="">Select</option>
                                                    @foreach($itemTypes as $id =>$val)
                                                    <option value="{{ $id }}">{{ $val }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="end_date" class="form-label">Brand Type</label>
                                                <select name="asset_brand" id="asset_brand" class="form-control">
                                                    <option value="">Select</option>
                                                    @foreach($BrandTypes as $id =>$val)
                                                    <option value="{{ $id }}">{{ $val }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                             <div class="col-md-3">
                                                <label for="start_date" class="form-label">Item Name</label>
                                                <input type="text" class="form-control" id="asset_name"  name="asset_name" placeholder="Item Name" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="work_mode" class="form-label">Serial Number</label>
                                                <input type="text" name="serial_number" id="serial_number" placeholder="Serial Number" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="submit" name="save" id="save" class="btn btn-primary w-100 download_punch_attendance">
                                                    Save
                                                </button>
                                            </div>
                                    </div>
                                    </div>
                                </form>
                            </div>
                    <table id="AssetsTable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sno </th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Serial</th>
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
          <!-- Modal -->

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
                     { data: 'asset_status', name: 'asset_status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
            });
        });
    </script>
<!-- <script src="{{ asset('assets/js/common.js') }}"></script> -->
<script src="{{ asset('assets/js/inventory.js') }}"></script>
@endsection