    
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
        <style>
      .nav-tabs .nav-item.show .nav-link, .nav-tabs .nav-link.active {
         color: white !important;
        background-color: #417cc5 !important;
        border-color: #dee2e6 !important;
        font-weight: bold;
}
            </style>

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
                        <div class="metric-icon bg-warning me-3">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <a href="{{ route('tasks.manage_projects.filter', 'active') }}"  style="text-decoration:none;">
                        <div>
                            <h3 class="metric-number text-warning">{{ $data['active_proj'] }}</h3>
                            <p class="metric-label">Active Projects</p>
                        </div>
                        </a>
                    </div>
                </div>
            </div>
           <!--- <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="metric-icon bg-info me-3">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div>
                            <h3 class="metric-number text-info">{{ $data['task_complete_cnt'] }}%</h3>
                            <p class="metric-label">Task Completion</p>
                        </div>
                    </div>
                </div>
            </div>-->
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
                                        <h5 class="mb-0"><i class="fas fa-project-diagram text-primary me-2"></i>Bar Chart</h5>
                                    </div>
                                    
                                  <ul id="project-tabs" class="nav nav-pills mb-4 gap-2" role="tablist">
                                     {{-- <button onclick="renderChart('All Projects')">All Projects</button> --}}
                                        @foreach(array_keys($projects) as $index => $proj)
                                            <li class="nav-item" role="presentation">
                                                <button 
                                                    onclick="renderChart('{{ $proj }}')" 
                                                    class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                                    id="tab-{{ Str::slug($proj, '-') }}" 
                                                    data-bs-toggle="tab" 
                                                    type="button" 
                                                    role="tab"
                                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                                    {{ $proj }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                <div id="timesheet-chart"></div>
                    </div>
                <!--Bar Chart --->
                <div class="chart-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="fa-solid fa-users-between-lines text-primary me-2"></i>Recent Activities of Employees</h5>
                    </div>
                     <form id="SearchForm" method="POST" action="{{ route('timesheet_search') }}">
                        @csrf
                        <div class="row">
                        <div class="form-group col-md-4">
                            <label>Project</label>
                                <select name="project_id" id="project_id" class="form-control">
                                    <option value="">Select Project</option>
                                    @foreach($projects_col as $projs)
                                     <option value="{{ $projs->id }}">{{ $projs->proj_name}}</option>
                                    @endforeach
                                </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Employee</label>
                                <select name="emp_id" id="emp_id" class="form-control">
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $id =>$emps)
                                     <option value="{{ $id }}">{{ $emps }}</option>
                                    @endforeach
                                </select>
                        </div>
                        <div class="form-group col-md-2">
                            <button type="submit" class="btn btn-primary w-100" style="margin: 23px 0 0 0;">Search</button>
                        </div>
                        </div>
                     </form>
                     <table id="searchTable" class="display table table-bordered">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Project Name</th>
                                <th>Module Name</th>
                                <th>Task Name</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                     </table>
                </div>
                <!-- Active Projects -->
                <div class="chart-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="fas fa-project-diagram text-primary me-2"></i>Active Projects</h5>
                        <a href="{{ route('tasks.manage_projects.filter', 'active') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="row g-3">
                           @foreach($completionStats as $indiv)
                        <div class="col-md-6">
                            <a href="{{ route('tasks.active_projects_tasks',$indiv['project_id'] ) }}" style="text-decoration: none;">
                            <div class="project-card priority-high p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ $indiv['project_name'] }}</h6>
                                    
                                </div>
                                <div class="progress progress-custom mb-2">
                                    <div class="progress-bar {{ $indiv['tag'] }}" style="width: {{ $indiv['completion_percent'] }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $indiv['completion_percent'] }}% Complete</small>
                                    <div class="d-flex">
                                        @foreach($indiv['random_member_images'] as $image)
                                        <img src="{{url('images/'.$image)}}" class="team-avatar me-1" alt="Team member">
                                        @endforeach
                                        @if($indiv['has_more_than_three_members']==true)
                                        <span class="badge bg-secondary rounded-pill">+{{ $indiv['members_cnt'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            </a>
                        </div>
                            @endforeach
                    </div>
                </div>
                 <div class="chart-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="fa-solid fa-bugs text-primary me-2"></i>Tickets Info</h5>
                    </div>
                    <div class="row g-3">
                          
                        <div class="col-md-12">
                            <table class="table">
                        <thead>
                          <tr>
                            <th> Assignee </th>
                            <th> Ticket Info </th>
                            <th> Status </th>
                            <th> Raised By </th>
                            
                          </tr>
                        </thead>
                        <tbody>
                            @if($ticket_info->isNotEmpty())
                            @foreach ($ticket_info as $ticket)
                            <tr>
                            <td>
                                @if($ticket->AssignedTicketMembers->isNotEmpty())
                                    {{ $ticket->AssignedTicketMembers->pluck('user.name')->implode(', ') }}
                                @else
                                     Not Assigned
                                @endif
                            </td>
                            <td> {{ $ticket->ticket_name }} </td>
                            <td>
                                @php
                                  $badge = config('global.task_status_badges')[$ticket->ticket_status] ?? ['label' => 'Unknown', 'class' => 'badge-secondary'];  
                                @endphp 
                                
                                 <label class="badge {{ $badge['class'] }}">{{  $badge['label'] }}  </label>
                            </td>
                            <td> {{ $ticket->TicketOwner->name }} </td>
                            
                          </tr>
                                
                            @endforeach
                          
                          @endif
                        </tbody>
                      </table>
                        </div>
                          

                    </div>
                </div>

                <div class="chart-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>Leave / Permission Request</h5>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <table  class="table">
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
                                    <tr colspan="4" align="center"> 
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
            </div>

            <!-- Right Column -->
            <div class="col-xl-4">
               
                <div class="chart-container  mb-4 col-md-12">
                    <div class="card" style="border: none; box-shadow: 0 4px 8px rgba(0,0,0,0.2), 0 6px 20px rgba(0,0,0,0.19);">
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

                <!-- Recent Activities -->
                <div class="chart-container">
                    <h5 class="mb-4"><i class="fas fa-clock text-primary me-2"></i>Recent Activities</h5>
                    <div class="list-group list-group-flush">
                        @if($recent_activity->isNotEmpty())
                        @foreach($recent_activity as $activity)
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex align-items-start">
                                <div class="badge bg-success rounded-pill me-3 mt-1">
                                    <i class="fas {{ $activity['act_tag'] }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $activity['description'] }}</div>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="list-group-item border-0 px-0">
                        <b> No Recent Activities </b>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
          </div>
         @if(!in_array(session('role_id'),config('global.role_without_attendance')) ) 
            @include('layouts.includes.attendance_entry')   
        @endif
       <script>
  $(document).ready(function () {
    var table = $('#searchTable').DataTable({
        processing: true,
        serverSide: true,
        paging:false,
        dom: 't', 
         order: [[0, 'desc']],
          //pageLength: 4,
        //scrollX: true,
        ajax: {
            url: "{{ route('timesheet_search') }}",
            type: "POST",
            data: function (d) {
                d._token = "{{ csrf_token() }}";
                d.project_id=$('#project_id').val();
                d.emp_id=$('#emp_id').val();
            },
            error: function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
            }
        },columns: [
            { data: 'emp_name', title: 'Employee Name' },
            { data: 'project_name', title: 'Project' },
            { data: 'module', title: 'Module' },
            { data: 'task', title: 'Task', className: 'wrap-text' },
            { data: 'worked_time', title: 'Hours Spent' },   // human-readable
      ],
            language: {
                  emptyTable: "No timesheet records found for current selection."
            }
    });
     $('#project_id').on('change', function() {
            $.get('/get_mapped_employees/' + $('#project_id').val(), function(response) {
                $('#emp_id').empty().append('<option value="">Select Employee</option>');
                if (!$.isEmptyObject(response) || !response.length === 0) {
                        $.each(response, function(id, name) {
                            $('#emp_id').append('<option value="'+id+'">'+name+'</option>');
                        });
                    } else {
                        $('#emp_id').empty().append('<option value="">Select Module</option>');
                    }
            });
    });
    $('#SearchForm').on('submit', function(e) {
    e.preventDefault(); // ✅ stop normal form submit
    $('#searchTable').DataTable().ajax.reload(); // ✅ reload table with new params
});

});
            
            </script>
@endsection