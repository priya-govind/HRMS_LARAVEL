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
                      Edit TimeSheet of {{ $user_name }} for the Date: {{ $slot_date}}
                  </div>
               </div>
               <div id="success-message" class="alert alert-success" role="alert" style="display: none;margin: 2% 0 0 3%;width: 94%;"></div>
          
                  <div class="card-body timesheet">
                            {{-- <div align="right">
                                <a href="{{ route('attendance.attendance_info') }}">Go To Employee Attendance Info</a>
                            </div><br/> --}}
                     <form id="timesheetForm">
                      <input type="hidden" id="date" name="date" value="{{$date}}">
                        <div id="formContainer"></div>
                           <button type="submit" class="btn btn-primary">Submit</button>
                      </form>

                  </div>
               
          </div>
        </div>
          
       </div>
      </div>
 <script>
    window.savedSlots = @json($savedSlots);
     const userCheckInTime = "{{ session('chkin_time') }}";
     const permissionRanges = @json($permissionSlots);  
     const isEditable = "true";
     //const isToday = "{{ $date }}" === "{{ \Carbon\Carbon::now()->toDateString() }}";
</script>
<script src="{{ asset('assets/js/timesheet.js') }}"></script>         
          @endsection
         