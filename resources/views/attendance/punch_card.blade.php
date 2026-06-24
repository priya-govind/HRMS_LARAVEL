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
    <div class="card-header">
        <h5 class="mb-0">Punch Card Management</h5>
    </div>
<hr/>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-warning  alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                {{ session('error') }}
            </div>
        @endif
        
        <div class="mb-4 border-bottom pb-3">
            <h6 class="mb-3"><i class="fa fa-upload" aria-hidden="true"></i> Import Punch Card Sheet</h6>
            <form method="POST" action="{{ route('import_punch_card') }}" id="PunchcardForm" enctype="multipart/form-data">
                @csrf
                <div class="card" style="padding:40px;">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="work_mode" class="form-label">Select Work Mode</label>
                            <select name="work_mode" id="work_mode" class="form-select">
                                <option value="">Select</option>
                                <option value="1">Punch Info Sheet</option>
                                <option value="2">Work From Home</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="work_mode" class="form-label">Select Month</label>
                            <select name="month" id="month" class="form-select">
                                <option value="">Select</option>
                                <option value="January">January</option>
                                <option value="February">February</option>
                                <option value="March">March</option>
                                <option value="April">April</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="August">August</option>
                                <option value="September">September</option>
                                <option value="October">October</option>
                                <option value="November">November</option>
                                <option value="December">December</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="work_mode" class="form-label">Select Team Type</label>
                            <select name="team_type" id="team_type" class="form-select">
                                <option value="">Select</option>
                                <option value="Development">Development</option>
                                <option value="IT">IT</option>
                                <option value="TDS">TDS</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="file" class="form-label">Select File to Upload</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">Import</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div>
           <div class="mb-4 pb-3"> 
            <h6 class="mb-3"><i class="fa fa-download" aria-hidden="true"></i> Export Attendance Report</h6>
            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1050;">
                <div id="success-toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="margin-top:60px;">                   <div class="d-flex">
                        <div id="success-toast-body" class="toast-body align-items-center">
                            Status updated successfully.
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>

                <div id="error-toast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" style="margin-top:60px">
                    <div class="d-flex">
                        <div id="error-toast-body" class="toast-body">
                            An error occurred.
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
             <form name="export_attendance" method="post" action="{{ route('attendance.export') }}" id="export_attendance">
                @csrf
                <div class="card" style="padding:40px;">
                <div class="row g-3">
                        <div class="col-md-1">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="{{ request('start_date') }}" readonly>
                        </div>
                        <div class="col-md-1">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="text" class="form-control" id="end_date" name="end_date" placeholder="End Date" value="{{ request('end_date') }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Employee Name</label>
                            <select id="emp_name" name="emp_name[]" class="form-multi-select border-primary" multiple data-coreui-search="true" data-coreui-cleaner="false">
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->employee_code }}"
                                        {{ is_array(request('emp_name')) && in_array($employee->employee_code, request('emp_name')) ? 'selected' : '' }}>
                                        {{ $employee->employee_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="work_mode" class="form-label">Working Mode</label>
                            <select class="form-select attendance-status" id="work_mode" name="work_mode">
                                <option value="">Select</option>
                                <option value="WFH">Work From Home </option>
                                <option value="A">Absent </option>
                                <option value="½P">Half Day Present </option>
                                
                            </select>
                        </div>
                        <div class="col-md-2">
                             <button type="button" class="btn btn-primary w-100 generate_report">
                                    Generate Report <i class="fa fa-table ms-1"></i>
                             </button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 download_punch_attendance">
                                Export to Excel <i class="fa fa-download ms-1"></i>
                            </button>
                        </div>
                </div>
                {{-- style="max-height: 400px;" --}}
                 <div id="attendance_table_container" class="mt-4 overflow-auto" ></div>
                </div>
             </form>
           </div>
        </div>
    </div>
  
          </div>
          <script>
$('#status-success-toast').show();
                $("#start_date").datepicker({
                    dateFormat: "dd-mm-yy",
                    onSelect: function(selectedDate) {
                        var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate);
                        var minDate = new Date(dateObj);
                        minDate.setDate(minDate.getDate() + 1);
                        $("#end_date").datepicker("option", "minDate", minDate);
                    }
                });

                $("#end_date").datepicker({
                    dateFormat: "dd-mm-yy"
                });
               $("#PunchcardForm").validate({
                    errorClass: "is-invalid",
                    rules: {
                        work_mode: {
                            required: true, 
                        },
                        file: {
                            required: true,
                        },
                        month:{
                            required: true,
                        },
                        team_type:{
                             required: true,
                        },
                    },
                    messages: {
                        work_mode: {
                            required: "Select Work mode.", 
                        },
                        file: {
                            required: "Upload File is Required.",
                        },
                         month:{
                            required: 'Select The Month',
                        },
                         team_type:{
                             required: "Select Team Type",
                        },
                    },
                    highlight: function (element, errorClass) {
                        $(element).addClass(errorClass);
                    },
                    unhighlight: function (element, errorClass) {
                        $(element).removeClass(errorClass);
                    },
                    errorPlacement: function (error, element) {
                        error.appendTo(element.parent());
                    },
                    submitHandler: function (form) {
                        form.submit();
                    },
                });
$(document).on('click', '.generate_report', function(e) {
    e.preventDefault();

    $.ajax({
        url: "{{ route('attendance.generateReport') }}",
        type: "POST",
        data: $('#export_attendance').serialize(),
        success: function(response) {
            $('#attendance_table_container').html(response.table_html);
        }
    });
});
$(document).on('change', '.attendance-status_report', function() {
    let recordId = $(this).data('id');
    let newStatus = $(this).val();
    
    $.ajax({
        url: "{{ route('attendance.updateStatus') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id: recordId,
            status: newStatus
        },
        success: function (response) {
            if (response.success) {
                // Update text inside success toast body
                $('#success-toast-body').text(response.success);
                
                // Initialize and show Bootstrap Toast
                let successToast = new bootstrap.Toast($('#success-toast')[0], { delay: 4000 });
                successToast.show();
            }
        },
        error: function (xhr) {
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            
            // Update text inside error toast body
            $('#error-toast-body').text('Error: ' + errorMessage);
            
            // Initialize and show Bootstrap Toast
            let errorToast = new bootstrap.Toast($('#error-toast')[0], { delay: 4000 });
            errorToast.show();
        }
    });
});
        //   $('.download_punch_attendance').on('click',function(){
        //        const downloadUrl = '{{ route('attendance.export') }}';
        //         window.location.href = downloadUrl;
        //     });
                    
         </script>
          @endsection
