@extends('layouts.app')
@section('content')
      <!-- partial:partials/_navbar.html -->
      @include('layouts.includes.topbar')
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('layouts.includes.sidebar')
        <style>
            .modal-dialog {
                max-height: 90vh;   /* 80% of viewport height */
                overflow-y: auto;
            }
            .btn{
              position: relative;
              z-index: 10;
            }
        </style>
        <!-- partial -->
        <div class="main-panel">
        <div class="content-wrapper">
            <div class="row justify-content-center mt-4">
          <div class="col-md-12">
              <div class="card shadow-sm">
                <div class="card-header">
                  <div class="float-start">
                    Timesheet Log
                </div>
                @if($DisableTimesheet==false)
                <div class="float-end">
                    <button type="button" class="btn btn-primary" id="addTimesheetBtn" data-toggle="modal" data-target="#timesheetModal">
                        Add New Timesheet
                    </button>
                </div>
                @endif 
                </div>
                <div class="card-body">
                  <div id="success-message" class="alert alert-success"  role="alert"  style="display: none;"></div>
           <div id="error-message" class="alert alert-danger" style="display: none;"></div>
            @if((in_array(session('role_id'),config('global.monitor_employees_act'))))
                    <form method="POST" id="dataForm" action="{{ route('timesheet_log_action') }}">
                         @csrf        
                            <div class="row align-items-center mb-3 alert alert-info" style="width:86%;">
                                    <div class="col-md-2">
                                  <input type="radio" name="status_type" value="2" checked="checked">
                                  Employee's Timesheet
                                </div>
                                <div class="col-md-2">
                                    <input type="radio" name="status_type" value="1">
                                  Your Timesheet </div>
                            </div>
                    <div class="row form-row align-items-start">
                         <div class="form-group">
                            <div class="row">
                                <div class="col-md-1  my-0">
                                <label for="startDate">From Date</label>
                                <input type="text" class="form-control" id="FromDate" name="FromDate" placeholder="From Date" readonly>
                              </div>
                              <div class="col-md-1  my-0">
                                <label for="endDate">To Date</label>
                                <input type="text" class="form-control" id="ToDate" name="ToDate" placeholder="To Date" readonly>
                              </div>
                            <div class="col-md-2 my-4">
                               <select name="proj_id"  id="search_project_id" class="form-control">
                                        <option value="">Select Project </option>
                                      @foreach($projects as $proj_id => $proj_name)
                                      <option value="{{ $proj_id }}">{{ $proj_name }}</option>
                                    @endforeach
                              </select> 
                            </div>
                             <div class="col-md-2  my-4">
                                <select name="module_id" id="search_module_id" class="form-control">
                                  <option value="">Select Module</option>
                                </select>
                              </div>
                               @if(in_array(session('role_id'),config('global.monitor_employees_act')))    
                              <div class="col-md-2  my-4 emp_stat">
                              <select name="emp_id" id="drp_emp_id"  class="form-control">
                                  <option value="">Select Employee </option>
                                  @foreach ($employees as $empe)
                                      <option value="{{ $empe->id}}">{{ $empe->name .'-'.$empe->roles->first()->role_name}}</option>    
                                  @endforeach
                              </select> 
                            </div>
                              @endif
                             <div class="col-md-1  my-4">
                              <button type="submit" class="btn btn-primary w-100">Submit</button>
                             </div>
                            <div class="col-md-2  my-4">
                              <button type="button"  id="exportBtn" class="btn btn-success">Export to Excel</button>
                             </div>
                              {{-- @if(in_array(session('role_id'),config('global.restriction_free_roles'))) --}}
                          </div> 
                  </form>
                  @else
                  <div class="float-end" style="padding: 0 0 28px 0;">
                    <button type="button"  id="exportBtn" class="btn btn-success">Export to Excel</button>
                      @if(session('user_id')==62)
                             
                                <button type="button" id="sendReportBtn" class="btn btn-warning">Send Report</button>
                            
                            @endif
                  </div>
                  @endif
                    <h4>Timesheet</h4>
                   <table id="categoryTable" class="display table table-bordered">
                        <thead>
                            <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Name</th>
                            <th>Project</th>
                            <th>Module</th>
                            <th>Task</th>
                            <th>Start Time</th>
                            <th>End Time </th>
                            <th>Hours Spent</th>
                            <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                            <tr>
                            <th colspan="9" style="text-align:right">Total Hours:</th>
                            <th></th>
                            </tr>
                        </tfoot>
                  </table>
                </div>
              </div>
          </div>
            </div>
        </div>
        <div class="modal fade" id="sendReportModal" tabindex="-1" aria-labelledby="sendReportModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendReportModalLabel">Send Mail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-4">Select Reporting Members</div>
                      <div class="col-md-7"> 
                          <select name="recipients[]" id="recipients"  class="form-multi-select" multiple data-coreui-search="true">
                              @foreach($reporting_emps as $empe)
                                  <option value="{{ $empe->email }}">
                                      {{ $empe->name }} - {{ $empe->roles->first()->role_name }}
                                  </option>
                              @endforeach
                          </select>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmSendReport" class="btn btn-primary">Send</button>
                </div>
            </div>
          </div>
      </div>
  @php
    $chkinTime = session('chkin_time'); // e.g. "10:00 AM"
    $fromTimes = ["9:00 AM","10:00 AM","11:00 AM","12:00 PM","1:00 PM","1:30 PM","2:00 PM","3:00 PM","4:15 PM","5:00 PM"];
    $toTimes   = ["10:00 AM","10:45 AM","12:00 PM","1:00 PM","1:30 PM","2:00 PM","3:00 PM","4:00 PM","5:00 PM","6:00 PM"];
  @endphp

<div class="modal fade" id="timesheetModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <form id="timesheetForm" method="post" action="{{ route('timesheets.store') }}">
      @csrf
        <input type="hidden" name="id" id="entry_id" value="">
        <input type="hidden" name="time_mode" id="time_mode" value="add">
        <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="timesheetModalLabel">New Timesheet Entry</h5> 
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                  <div id="success-message1" class="alert alert-success"  role="alert"  style="display: none;"></div>
                  <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                  <input type="hidden" id="hidden_from" name="hidden_from" >
                    <input type="hidden" id="hidden_to" name="hidden_to" >

                  <!-- Date restricted to today -->
                  <div class="form-group">
                    <label>Date</label>
                    <select name="create_dt" id="create_dt" class="form-control">
                    </select>
                  </div>

                  <!-- From dropdown -->
                  <div class="form-group">
                    <label>From</label>
                      <select name="from_time" id="from_time" class="form-control">
                          @foreach($fromdispSlots as $time)
                              <option value="{{ $time }}">
                                  {{ $time }}
                              </option>
                          @endforeach
                      </select>
                  </div>
                  <!-- To dropdown -->
                  <div class="form-group">
                    <label>To</label>
                    <select name="to_time" id="to_time" class="form-control">
                            @foreach($toSlots as $time)
                            @if(strtotime($time) > strtotime($defaultFrom))
                                <option value="{{ $time }}" {{ $defaultTo == $time ? 'selected' : '' }}>
                                    {{ $time }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                  </div>

                  <!-- Project dropdown -->
                  <div class="form-group">
                    <label>Project</label>
                    <select name="project_id" id="project_id" class="form-control">
                      <option value="">Select Project</option>
                      @foreach($projects as $proj_id => $proj_name)
                          <option value="{{ $proj_id }}">{{ $proj_name }}</option>
                        @endforeach
                        <option value="other">Other (Create new Project)</option>
                    </select>
                    <div class="form-group" id="customProjectWrapper" style="display:none;">
                        <label for="custom_project">Custom Project</label>
                        <input type="text" id="custom_project" name="custom_project" class="form-control">
                    </div>
                  </div>

                  <!-- Module dropdown -->
                  <div class="form-group">
                    <label>Module</label>
                    <select name="module_id" id="module_id" class="form-control">
                      <option value="">Select Module</option>
                      <option value="other">Other (Create new Module)</option>
                    </select>
                    <div class="form-group" id="customModuleWrapper" style="display:none;">
                        <label for="custom_module">Custom Module</label>
                        <input type="text" id="custom_module" name="custom_module" class="form-control">
                    </div>
                  </div>

                  <!-- Task dropdown -->
                  <div class="form-group">
                    <label>Task</label>
                    <select name="task_id" id="task_id" class="form-control">
                      <option value="">Select Task</option>
                      <option value="other">Other (Create new task)</option>
                    </select>
                    <div class="form-group" id="customTaskWrapper" style="display:none;">
                        <label for="custom_task">Custom Task</label>
                        <input type="text" id="custom_task" name="custom_task" class="form-control">
                    </div>
                  </div>

                  <!-- Comments -->
                  <div class="form-group">
                    <label>Comments</label>
                    <textarea name="comments"  id="comments" class="form-control"></textarea>
                  </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Save Timesheet</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </form>
  </div>
</div>
        </div></div>
         <script>
  $(document).ready(function () {

    var table = $('#categoryTable').DataTable({
        processing: true,
        serverSide: true,
         order: [[0, 'desc']],
          //pageLength: 4,
        //scrollX: true,
        ajax: {
            url: "{{ route('timesheet_log_action') }}",
            type: "POST",
            data: function (d) {
                d._token = "{{ csrf_token() }}";
                d.FromDate = $('#FromDate').val();
                d.ToDate = $('#ToDate').val();
                d.proj_id=$('#search_project_id').val();
                d.module_id=$('#search_module_id').val();
                // Only include emp_id if showing employee mode
                  const statusType = $("input[name='status_type']:checked").val();
                 d.status_type=  statusType;
                  if (statusType == "2") {
                      d.emp_id = $('#drp_emp_id').val();
                  }

            },
            error: function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
            }
        },columns: [
    { data: 'create_dt', title: 'Date' },
    { data: 'day', title: 'Day' },
    { data: 'emp_name', title: 'Name' },
    { data: 'project_name', title: 'Project' },
    { data: 'module', title: 'Module' },
    { data: 'task', title: 'Task' },
    { data: 'start_time', title: 'Start Time' },
    { data: 'end_time', title: 'End Time' },
    { data: 'worked_time', title: 'Hours Spent' },   // human-readable
    { data: 'timesheet_dtls', title: 'Action' }
      ],
footerCallback: function (row, data, start, end, display) {
    var api = this.api();

    // Grand total from server JSON
    var totalMinutes = api.ajax.json().grand_total_minutes;

    // Page total from current page rows
    var pageMinutes = 0;
    data.forEach(function (row) {
        pageMinutes += parseInt(row.duration_minutes) || 0;
    });

    function formatDuration(minutes) {
        let h = Math.floor(minutes / 60);
        let m = minutes % 60;
        if (h && m) return `${h} hr ${m} mins`;
        if (h) return `${h} hr`;
        return `${m} mins`;
    }

    $(api.column(8).footer()).html(
        `Overall Total: ${formatDuration(totalMinutes)} (This Page: ${formatDuration(pageMinutes)})`
    );
},
            language: {
                  emptyTable: "No timesheet records found for current day."
            }
    });
    @if((in_array(session('role_id'),config('global.monitor_employees_act'))))
    $('.emp_stat').show();
    table.column(2).visible(true);  
    @else 
      $('.emp_stat').hide();
      table.column(2).visible(false);  
    @endif
  $('#exportBtn').on('click', function (e) {
      e.preventDefault();
      const from = $('#FromDate').val();
      const to = $('#ToDate').val();
      const statusType = $("input[name='status_type']:checked").val();
      const proj_id = $('#search_project_id').val();
      const module_id = $('#search_module_id').val();
      const userId = {{ session('user_id') }};
    let emp_id='';
    let emp_name='';
    if (statusType == 2) {
       emp_id=$('#drp_emp_id').val();
       emp_name = $('#drp_emp_id').find('option:selected').text();
    } 
    let url = ''; 
    @if (in_array(session('role_id'),config('global.monitor_employees_act')))
        if(emp_id!='' && emp_name!='Select Employee'){
            url = `{{ route('timesheet_export') }}?FromDate=${from}&ToDate=${to}&proj_id=${proj_id}&module_id=${module_id}&emp_id=${emp_id}&emp_name=${emp_name}&status_type=${statusType}`;
        } else {
            url = `{{ route('timesheet_export') }}?FromDate=${from}&ToDate=${to}`;
        }
        window.location.href = url;
    @else
        checkUserCheckout(userId).done(function(response) {
            if (response.checked_out) {
                console.log("Record found, proceeding with export...");
                      url = `{{ route('timesheet_export') }}`;
                    window.location.href = url;
            } else {
               $('#error-message').text("Please checkout before downloading timesheet.").show();
               setTimeout(() => {
                     $('#error-message').hide();
                    if (response.redirect_url)
                     window.location.href = response.redirect_url;
                }, 3000);
            }
        }).fail(function(xhr) {
            console.error("Error checking status:", xhr.responseText);
            alert("Something went wrong while checking status.");
        });
    @endif
  });
});
</script> 
<script src="{{ asset('assets/js/search_timesheet.js') }}"></script>
<script src="{{ asset('assets/js/pm_timesheet.js') }}"></script>
@endsection