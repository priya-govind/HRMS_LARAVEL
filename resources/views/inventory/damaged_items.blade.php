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
                         Damaged Inventory Items
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
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    </table> 
                </div>
                </div>
                </div>
            </div>
<script type="text/javascript">
        $(document).ready(function () {
        $('#AssetsTable').DataTable({
                processing: true,
                serverSide: true,
                  ajax: {
                        url: "{{ route('damaged_items_action') }}",
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