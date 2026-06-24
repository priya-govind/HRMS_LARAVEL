    
@extends('layouts.app')


<script>
    var csrfToken = "{{ csrf_token() }}"; 
</script>
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
            <div class="page-header">
              <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                  <i class="mdi mdi-home"></i>
                </span> Dashboard
              </h3>
            </div>
            <div class="row">
            <div class="col-3">
                <h5 class="card-title">Welcome {{ session('user_name')  }}</h5><br/>
                @if (session('message'))
                            {{ session('message') }}<br/><br/>
                    @endif
                </div>
            </div>
       
        <div class="row g-4 mb-4">
            <div class="col-xl-4 col-lg-9 col-md-9">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="metric-icon bg-success me-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="{{ route('employees') }}" style="text-decoration:none;">
                        <div>
                            <h3 class="metric-number text-success">{{ $data['total_emp'] }}</h3>
                            <p class="metric-label">Total Employees</p>
                        </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-9 col-md-9">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="metric-icon bg-primary me-3">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <a href="{{ route('employees.filter', 'present') }}" style="text-decoration:none;">
                            <div>
                                <h3 class="metric-number text-primary">{{ $data['present_emp'] }}</h3>
                                <p class="metric-label">Present Today</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-9 col-md-9">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="metric-icon bg-danger me-3">
                            <i class="fa-solid fa-user-xmark"></i>
                        </div>
                        <a href="#"  style="text-decoration:none;">
                        <div>
                            <h3 class="metric-number text-danger">{{ $data['absent'] }}</h3>
                            <p class="metric-label">Absent</p>
                        </div>
                        </a>
                    </div>
                </div>
            </div>
   
        </div>
        


        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-xl-8">
                <!-- Attendance Overview -->
                <div class="chart-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>Today's Attendance</h5>
                        <span class="badge bg-success">{{ $data['present_percent'] }}% Present</span>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="attendance-meter">
                                <div class="meter-circle">
                                    <a href="{{ route('employees.filter', 'present') }}" style="text-decoration:none;">
                                    <div class="meter-inner">
                                        <div class="fw-bold fs-4">{{ $data['present_percent'] }}%</div>
                                        <small class="text-muted">Present</small>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-6">
                                    <a href="{{ route('employees.filter', 'present') }}" style="text-decoration:none;">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 text-success mb-0">{{ $data['present_emp'] }}</div>
                                        <small class="text-muted">Present</small>
                                    </div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('employees.filter', 'absent') }}" style="text-decoration:none;">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 text-danger mb-0">{{ $data['absent'] }}</div>
                                        <small class="text-muted">Leave</small>
                                    </div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('employees.filter', 'late') }}" style="text-decoration:none;">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 text-warning mb-0">{{ $data['late_emp'] }}</div>
                                        <small class="text-muted">Late</small>
                                    </div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('employees') }}/filter/permission" style="text-decoration:none;">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 text-info mb-0">{{ $data['permission'] }}</div>
                                        <small class="text-muted">Permission</small>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Bar Chart --->
                
                <div class="chart-container mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="mb-0"><i class="fas fa-project-diagram text-warning me-2"></i>Bar Chart</h5>
                                    </div>
             <ul class="nav nav-tabs" id="teamTypeTabs">
                    @foreach($teamTypes as $index => $type)
                        <li class="nav-item">
                            <a class="nav-link team-tab {{ $index === 0 ? 'active' : '' }}" data-id="{{ $type->id }}"
                            style="background: {{ $type->team_color }}; font-weight: bold; color:black;">
                                {{ $type->team_typ_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <canvas id="AttendanceChart" width="400" height="200" class="mt-3"></canvas>
                </div>
                <div class="chart-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>Leave / Permission Request</h5>
                        <a href="{{ route('attendance.leave_approval_requests') }}" class="badge bg-info" style="text-decoration: none;font-weight: 500;">View More...</a>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <table class="table">
                                <thead>
                                    <tr>
                                    <th>Employee Name</th>
                                    <th>Applied Date</th>
                                    <th>Leave Type</th>
                                    <th>Status</th>
                                    </tr>
                                </thead>
                            
                                <tbody>
                                     @if($leaves_info->isNotEmpty())
                                        @foreach($leaves_info as $indiv_leave)
                                        <tr>
                                                <td>{{ optional($indiv_leave->emp_name)->name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($indiv_leave->created_at)->format('d-m-Y') }}</td>
                                                <td>{{ ($indiv_leave->leave_type == 1) ? 'Leave' : 'Permission' }}</td>
                                                <td>@if ($indiv_leave->leave_status==0) 
                                                        <span class="badge blinking">Waiting for Approval</span>
                                                    @elseif($indiv_leave->leave_status==1)
                                                        <span class="badge badge-success"  data-bs-toggle="tooltip" data-bs-html="true" title="{{  htmlspecialchars_decode('Comments: ' . e($indiv_leave->reason_status)) }}">Approved</span>
                                                    @elseif($indiv_leave->leave_status==2)
                                                        <span class="badge badge-danger"  data-bs-toggle="tooltip" data-bs-html="true" title="{{  htmlspecialchars_decode('Comments: ' . e($indiv_leave->reason_status)) }}">Rejected</span>
                                                    @else
                                                        <span class="badge badge-secondary">Unknown</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                    <tr colspan="4"> 
                                        <td>
                                            No Leave / Permission Request Found.
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                                </table>
                        </div>
                    </div>
                </div>
                <div class="chart-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>Upcoming Birthdays</h5>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <table class="table">
                                <thead>
                                    <tr>
                                    <th>Employee Name</th>
                                    <th>Employee Code</th>
                                    <th>Birth date</th>
                                    </tr>
                                </thead>
                            
                                <tbody>
                                    @if($upcome_birth->isNotEmpty())
                                        @foreach($upcome_birth as $indiv_birth)
                                        <tr>
                                                <td>{{ $indiv_birth->employee_name }}</td>
                                                <td>{{ $indiv_birth->employee_code }} </td>
                                                <td>{{ \Carbon\Carbon::parse($indiv_birth->birth_date)->format('d-m-Y') }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                    <tr colspan="4"> 
                                        <td>
                                            No Upcoming Birthdays for the current Month.
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                                </table>
                        </div>
                    </div>
                </div>




            </div>

            <!-- Right Column -->
            <div class="col-xl-4">
                <!-- Team Status -->
                
                    <div class="chart-container mb-4 card" style="border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.2), 0 6px 20px rgba(0,0,0,0.19);">
                        <div class="card-body">
                            <h5 class="card-title">Chats</h5>

                            <div class="input-group mb-3" style="box-shadow: inset 2px 2px 6px #d1d9e6, inset -2px -2px 6px #ffffff; border-radius: 10px;">
                                <span class="input-group-text bg-white" style="border-radius: 10px 0 0 10px;">
                                    <i class="mdi mdi-magnify"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Search Contacts or Messages..." id="search-contacts" style="border-radius: 0 10px 10px 0; color: black; border: 1px solid gray">
                            </div>

                            <div class="d-flex justify-content-start align-items-center mb-3" style="gap: 70px; margin-left: 70px; font-size: 14px; color:rgb(72, 37, 153);">
                                <div id="allChatsTab" class="tab-header active" style="cursor: pointer; color:rgb(72, 37, 153);" onclick="switchChatView('all')">
                                    <i class="mdi mdi-message-processing"></i> All messages
                                </div>
                                <div id="unreadChatsTab" class="tab-header" style="cursor: pointer; font-weight: normal; color:rgb(72, 37, 153);" onclick="switchChatView('unread')">
                                    <i class="mdi mdi-message-minus"></i>  Unread messages
                                </div>
                            </div>

                            <ul class="list-group" id="search-results"></ul>
                        </div>
                    </div>

                <!-- Quick Actions -->
                {{-- <div class="chart-container mb-4">
                    <h5 class="mb-4"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('generate_report') }}" class="quick-action-btn text-center">
                                <i class="fas fa-file-alt fa-2x mb-2 d-block"></i>
                                <small>Generate Report</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" class="quick-action-btn text-center">
                                <i class="fas fa-plus fa-2x mb-2 d-block"></i>
                                <small>New Project</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" class="quick-action-btn text-center">
                                <i class="fas fa-calendar fa-2x mb-2 d-block"></i>
                                <small>Schedule Meeting</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="#" class="quick-action-btn text-center">
                                <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                                <small>Analytics</small>
                            </a>
                        </div>
                    </div>
                </div> --}}
            </div>

        </div>
          </div>
           @if (session('show_birthday_alert') === true)
             <div class="modal fade" id="BirthAlertModal" tabindex="-1" aria-labelledby="BirthAlertModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="BirthAlertModalLabel">Birthday Remainder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img src="{{asset('assets/images/celebrate.gif')}}" style="height: 20%;width: 20%;text-align: center;/* padding: 0; */margin: 0 0 0 37%;">
                        <div id="info" style="font-size: 20px;color: green;font-family: cursive;">
                        </div>
                    </div>
                </div>
              </div>
             </div>
           @endif
@endsection