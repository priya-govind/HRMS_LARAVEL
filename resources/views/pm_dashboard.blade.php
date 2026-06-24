    
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
            <div class="col-xl-3 col-lg-6 col-md-6">
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
            <div class="col-xl-3 col-lg-6 col-md-6">
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
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="metric-icon bg-warning me-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="{{ route('employees.filter', 'Team') }}" style="text-decoration:none;">
                        <div>
                            <h3 class="metric-number text-warning">{{ $data['tot_team'] }}</h3>
                            <p class="metric-label">Total Team Members</p>
                        </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="metric-icon bg-primary me-3">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <a href="{{ route('employees.filter', 'PresentTeam') }}" style="text-decoration:none;">
                            <div>
                                <h3 class="metric-number text-primary">{{ $data['present_team_emp'] }}</h3>
                                <p class="metric-label">Team Members Present Today</p>
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
                        <h5 class="mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>Task Completion Status</h5>
                        <span class="badge bg-success">{{ $data['task_complete_cnt'] }}% Completed</span>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="attendance-meter">
                                <div class="meter-circle">
                                    
                                    <div class="meter-inner">
                                        <div class="fw-bold fs-4">{{ $data['task_complete_cnt'] }}%</div>
                                        <small class="text-muted">Completed</small>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-6">
                                     <a href="{{ route('tasks.manage_tasks.filter', 'Completed') }}" style="text-decoration:none;">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 text-success mb-0">{{ $data['completed_tasks'] }}</div>
                                        <small class="text-muted">Completed</small>
                                    </div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('tasks.manage_tasks.filter', 'In_Progress') }}" style="text-decoration:none;">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 text-danger mb-0">{{ $data['inprogress_tasks'] }}</div>
                                        <small class="text-muted">Inprogress</small>
                                    </div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('tasks.manage_tasks.filter', 'Re_open') }}"  style="text-decoration:none;">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 text-warning mb-0">{{ $data['reopen_tasks'] }}</div>
                                        <small class="text-muted">Reopened</small>
                                    </div>
                                    </a>
                                </div>
                                <div class="col-6">
                                   <a href="{{ route('tasks.manage_tasks.filter', 'Not_Started') }}"  style="text-decoration:none;">
                                    <div class="text-center p-3 bg-light rounded">
                                        <div class="h4 text-info mb-0">{{ $data['notstart_tasks'] }}</div>
                                        <small class="text-muted">Not Started</small>
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
                                        <ul class="nav nav-tabs" role="tablist">
                                        @foreach($projectCharts as $projectName => $chart)
                                            <li class="nav-item">
                                            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#proj-{{ Str::slug($projectName) }}">
                                                {{ $projectName }}
                                            </button>
                                            </li>
                                        @endforeach
                                        </ul>
                                        <div class="tab-content">
                                        @foreach($projectCharts as $projectName => $chart)
                                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="proj-{{ Str::slug($projectName) }}">
                                            <canvas id="chart-{{ Str::slug($projectName) }}"></canvas>
                                            </div>
                                        @endforeach
                                        </div>
                </div>
                <!--Bar Chart --->
                <!-- Active Projects -->
                <div class="chart-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="fas fa-project-diagram text-warning me-2"></i>Active Projects</h5>
                        <a href="{{ route('tasks.manage_projects.filter', 'active') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="row g-3">
                        @forelse($completionStats as $indiv)
                            <div class="col-md-6">
                                <a href="{{ route('tasks.active_projects_tasks', $indiv['project_id']) }}" style="text-decoration: none;">
                                    <div class="project-card priority-high p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">{{ $indiv['project_name'] }}</h6>
                                        </div>

                                        <div class="progress progress-custom mb-2">
                                            <div class="progress-bar {{ $indiv['tag'] }}" style="width: {{ $indiv['completion_percent'] }}%;" role="progressbar" aria-valuenow="{{ $indiv['completion_percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">{{ $indiv['completion_percent'] }}% Complete</small>
                                            <div class="d-flex align-items-center">
                                                @foreach($indiv['random_member_images'] as $image)
                                                    <img src="{{ url('images/' . $image) }}" class="team-avatar me-1" alt="Team member avatar" title="Team member">
                                                @endforeach

                                                @if($indiv['has_more_than_three_members'])
                                                    <span class="badge bg-secondary rounded-pill">+{{ $indiv['members_cnt'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted">
                                No Active Projects
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-xl-4">
               
                <div class="chart-container col-md-12">
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
                        @if($recent_activity->isEmpty())
                            <p>No recent activity found.</p>
                        @else
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
                        
                        @endif
                    </div>
                </div>
            </div>
        </div>
          </div>
        @include('layouts.includes.attendance_entry')   
@endsection