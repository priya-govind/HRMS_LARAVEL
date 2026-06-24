@extends('layouts.app')
@section('content')      
<meta name="csrf-token" content="{{ csrf_token() }}">
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
               <div class="card-header bg-primary text-white">
                  <div class="float-start">
                      View TimeSheet of {{ $user_name }} for the Date: {{ $slot_date}}
                  </div>
                 
               </div>
               <div id="success-message" class="alert alert-success" role="alert" style="display: none;margin: 2% 0 0 3%;width: 94%;"></div>
               
                  <div class="card-body timesheet">

                  <div class="float-end">
                    <button class="btn btn-primary btn-sm my-5" id="exportBtn" data-id="{{ \Carbon\Carbon::parse($slot_date)->format('d-m-Y')}}">
                       Download as Excel
                    </button>
                  </div>
                  <br/><br/>
                    <table class="display table table-bordered">
                      <thead>
                      <tr>
                        <td>Date</td>
                        <td>Day</td>
                        <td>Project</td>
                        <td>Module</td>
                        <td>Task</td>
                        <td>Timings</td>
                        <td>Comments</td>
                      </tr>
                      </thead>
                      <tbody>
                        @if($savedSlots)
                        @foreach($savedSlots as $slot)
                        <tr>
                       <td>{{ \Carbon\Carbon::parse($slot->create_dt)->format('d M,Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($slot->create_dt)->dayName }}</td>
                        <td>{{ $slot->project_id ? $slot->project->proj_name : $slot->custom_project }} </td>
                        <td>{{ $slot->module_id ? $slot->module->module_name  : $slot->custom_module }} </td>
                        <td>{{ $slot->task_id ? $slot->task->task_name : $slot->custom_task  }} </td>
                        <td>{{ $slot->from_time." - ". $slot->to_time  }} </td>
                        <td style="line-height:1.6;">{!! wordwrap($slot->comments, 70, "<br/>") !!}</td>
                        </tr>
                        @endforeach
                        <input type="hidden" name="emp_id" id="emp_id" value="{{ $slot->emp_id }}">
                        @endif
                      </tbody>
                    </table>
                            {{-- <div align="right">
                                <a href="{{ route('attendance.attendance_info') }}">Go To Employee Attendance Info</a>
                            </div><br/> 
                     <form id="timesheetForm">
                        <div id="formContainer"></div>
                          
                      </form>--}}

                  </div>
               
          </div>
        </div>
          
       </div>
      </div>
 {{-- <script>
    window.savedSlots = @json($savedSlots);
     const userCheckInTime = "{{ session('chkin_time') }}";
     const permissionRanges = @json($permissionSlots);  
     const isEditable = "{{ $date === \Carbon\Carbon::now()->toDateString() ? 'true' : 'false' }}";
     const isToday = "{{ $date }}" === "{{ \Carbon\Carbon::now()->toDateString() }}";
</script>
<script src="{{ asset('assets/js/view_timesheet.js') }}"></script>          --}}
<script>
  $(document).ready(function () {
        $(document).on('click', '#exportBtn', function (e) {
            e.preventDefault();
            var given_date = $(this).data('id');
              emp_id=$('#emp_id').val();
            let url = '';
              if(emp_id!=''){
                 let url = "{{ route('timesheet_export') }}"+ `?GivenDate=${given_date}&emp_id=${emp_id}` ;
                  window.location.href = url;
              }
              
           
       });
  });
  </script>
          @endsection
         