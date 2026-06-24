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
            <div class="row justify-content-center mt-4">
          <div class="col-md-12">
              <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: #215dab">
                  <h5 class="mb-0">Export Inventory Report</h5>
                </div>
                <div class="card-body">
                  <form  id="inventoryReport">
                    @csrf
                    <div class="row form-row align-items-start">
                      <div class="form-group col-md-2">
                          <label for="teamType"> Inventory Type</label>
                            <select class="form-control" id="inventory_type" name="inventory_type">
                              <option value="">Select Inventory Type</option>
                                @foreach ($itemTypes as $id =>$name)
                                  <option value="{{ $id  }}" {{ request('inventory_type') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                      </div>
                      <div class="form-group col-md-2">
                          <label for="teamType">Brand</label>
                            <select class="form-control" id="brand" name="brand">
                              <option value="">Select Brand</option>
                                @foreach ($BrandTypes as $id =>$brand)
                                  <option value="{{ $id  }}" {{ request('brand') == $id ? 'selected' : '' }}>{{ $brand }}</option>
                                @endforeach
                            </select>
                      </div>
                      <div class="form-group col-md-2">
                          <label for="teamType">Assigned Employees</label>
                            <select class="form-control" id="user_id" name="user_id">
                              <option value="">Select Employee</option>
                                @foreach ($user_info as $id =>$u_name)
                                  <option value="{{ $id  }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $u_name }}</option>
                                @endforeach
                            </select>
                      </div>
                      
                      <div class="form-group col-md-2 d-flex align-items-end" style="margin: 23px 0 0 0;">
                        <button type="submit" class="btn btn-primary w-100">Generate</button>
                      </div>
                    <div class="form-group col-md-2" style="margin: 23px 0 0 0;">
                          <button class="btn btn-primary download_tasks">Export to excel 
                            <i class="fa fa-download" aria-hidden="true"></i>
                          </button>
                    </div>
                    </div>
                  </form>
                  <table id="inventoryReportTable" class="table table-bordered">
                    <thead>
                      <tr>
                        <th>S.no</th>
                        <th>Inventory Type</th>
                        <th>Brand Name</th>
                        <th>Inventory Name</th>
                        <th>Serial Number</th>
                        <th>Assigned Employee Name</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
              
          </div>
           

            </div>
        </div>

     
   <script>
$(document).ready(function () {
let table = $('#inventoryReportTable').DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "{{ route('inventory_report_action') }}",
    type: "POST",
    data: function(d) {
      d._token = "{{ csrf_token() }}";
      d.inventory_type = $('#inventory_type').val();
      d.brand = $('#brand').val();
      d.user_id = $('#user_id').val();
    }
  },
  columns: [
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'inventory_type', name: 'inventory_type' },
    { data: 'brand', name: 'brand' },
    { data: 'inventory_name', name: 'inventory_name' },
    { data: 'serial_number', name: 'serial_number' },
    { data: 'assigned_employee', name: 'assigned_employee' }
  ]
});
});
  $('#inventoryReport').on('submit', function(e) {
  e.preventDefault(); // Prevent default form submission
  $('#inventoryReportTable').DataTable().ajax.reload(); // Reload DataTable with new filters
});
   $('.download_tasks').on('click',function(){
    const inventory_type =  $('#inventory_type').val();
    const brand = $('#brand').val();
    const user_id = $('#user_id').val();
    const downloadUrl = `/inventory_report_export?inventory_type=${inventory_type}&brand=${brand}&user_id=${user_id}`;
   // alert(downloadUrl);
    window.location.href = downloadUrl;
    });
</script>

<style>
  .form-control:disabled, .form-control:read-only {
    background-color: white;
  }
</style>   
          @endsection 