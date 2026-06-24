@extends('layouts.app')
@section('content')
      
      <!-- partial:partials/_navbar.html -->
      @include('layouts.includes.topbar')
     
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('layouts.includes.sidebar')
 
        <!-- partial -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="main-panel">
        <div class="content-wrapper">
                 <div class="row justify-content-center mt-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header  bg-primary text-white">
                                <div class="float-start">
                                Attendance Information
                                </div>
                            </div>
                            <div class="card-body">
                                  @if(in_array(session('role_id'),config('global.monitor_employees_act')))
                                <div class="row align-items-center mb-3">
                                    <div class="col-md-2">
                                        <input type="radio" name="status_type" value="1" checked="checked">
                                      Your Attendance </div>
                                       <div class="col-md-2">
                                      <input type="radio" name="status_type" value="2">
                                      Employee's Attendance
                                    </div>
                                </div>
                                <!-- <form method="GET"> -->
                              
                                    <div class="row align-items-center mb-3" id="emp_stat" style="display:none;">
                                    <div class="col-md-2">
                                        <label class="form-label mb-0" for="Search">Search By Employee Name:</label>
                                    </div>
                                    <div class="col-md-4">
                                       <select name="emp_id" id="drp_emp_id" >
                                        <option value="">Select Employee </option>
                                        @foreach ($employees as $empe)
                                            <option value="{{ $empe->id}}">{{ $empe->name .'-'.$empe->roles->first()->role_name}}</option>    
                                        @endforeach
                                       </select>
                                    </div>
                                    <!-- <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div> -->

                                    </div>
                                    @endif
                                
                                <div class="container mt-5">
                                    <div id="calendar"></div>
                                </div>
                            </div>
                     <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="taskModalLabel">Tasks Done</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Task content will be injected here -->
      </div>
    </div>
  </div>
</div>

                            </div>      
                        </div>
                    </div>
                </div>
      </div>
      <input type="hidden" id="employee_id" name="employee_id" value="{{ session('user_id') }}">
      <input type="hidden" id="user_id" name="user_id" value="{{ session('user_id') }}">
      </div>
  <!-- FullCalendar Global JS -->
  <script src="{{ asset('assets/js/index.global.min.js') }}"></script>
  <script src="{{ asset('assets/js/calendar.js') }}"></script>
  <script>
    window.savedSlots = @json($savedSlots);
    window.routes = {
    viewTimesheet: "{{ route('view_timesheet', ['prev_date' => '__DATE__', 'user_id' => '__USER__']) }}",
    timesheetExport: "{{ route('timesheet_export') }}"
  };

    const userCheckInTime = "{{session('chkin_time')}}"; 
    const permissionRanges = @json($permissionSlots); 
</script>
<script src="{{ asset('assets/js/timesheet.js') }}"></script>  
          @endsection
         



