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
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="{{ route('tasks.manage_tasks.filter', 'Completed') }}" style="text-decoration:none;">
                        <div>
                            <h3 class="metric-number text-success">{{ $taskCounts->completed_tasks }}</h3>
                            <p class="metric-label">Completed Tasks</p>
                        </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-9 col-md-9">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="metric-icon bg-primary me-3">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <a href="{{ route('tasks.manage_tasks.filter', 'In_Progress') }}" style="text-decoration:none;">
                            <div>
                                <h3 class="metric-number text-primary">{{ $taskCounts->inprogress_tasks }}</h3>
                                <p class="metric-label">Inprogress Tasks</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-9 col-md-9">
                <div class="card metric-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="metric-icon bg-danger me-3">
                            <i class="fas fa-hourglass-start"></i>
                        </div>
                        <a href="{{ route('tasks.manage_tasks.filter', 'Re_open') }}"  style="text-decoration:none;">
                        <div>
                            <h3 class="metric-number text-warning">{{ $taskCounts->reopen_tasks }}</h3>
                            <p class="metric-label">Reopened Tasks</p>
                        </div>
                        </a>
                    </div>
                </div>
            </div>
          @if(!$tasks->isEmpty())
            <div class="col-xl-8 col-lg-9 col-md-9">
              
                <div class="card metric-card h-100">
                  <div class="d-flex justify-content-between align-items-center  mx-4 mb-2 mt-5">
                        <h5 class="mb-0"><i class="fas fa-project-diagram text-warning me-2"></i>Tasks Info</h5>
                    </div>
                    <div class="card-body d-flex align-items-center">
                      
                        <div class="row">
                            @foreach($tasks as $task)
                                  <div class="col-md-6">
                                    <div class="card mb-4">
                                        <div class="card-body team_members_empdash">
                                            <h4 class="card-title">
                                                Remaining Members Associated with <strong>{{ $task->task_name }} - {{ $task->id}}</strong>
                                            </h4>

                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Employee Name</th>
                                                        <th>Team Name</th>
                                                        <th>Task Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($task->assignedNormalEmployees as $member)
                                                        <tr>
                                                            <td>{{ $member->employee->name ?? 'N/A' }}</td>
                                                            <td>{{ $member->team->team_name ?? 'N/A' }}</td>
                                                            @php
                                                                $badge = config('global.task_status_badges')[$member->emp_task_status] ?? ['label' => 'Unknown', 'class' => 'badge-secondary'];
                                                            @endphp
                                                            <td>
                                                              <label class="badge {{ $badge['class'] }}">
                                                                  {{ $badge['label'] }}
                                                              </label>
                                                          </td>                            
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                  </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if(session('support_access')==1)
              <div class="col-xl-8 col-lg-9 col-md-9">
                <div class="chart-container mb-4">
                      <div class="d-flex justify-content-between align-items-center mb-4">
                          <h5 class="mb-0"><i class="fas fa-project-diagram text-warning me-2"></i>Tickets Info</h5>
                          <a href="{{ url('tickets')}}" class="badge bg-primary" style="text-decoration: none;font-weight: 500;">View More...</a>
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
              </div>
              @endif
            <div class="col-xl-4 col-lg-9 col-md-9">
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
            </div>
          </div>
          </div>
         @include('layouts.includes.attendance_entry')    
          @endsection
         