@extends('layouts.app')
@section('content')
      
      <!-- partial:partials/_navbar.html -->
      @include('layouts.includes.topbar')
     
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('layouts.includes.sidebar')
        @php
    use App\Helpers\PermissionHelper;
@endphp
        <!-- partial -->
        <div class="main-panel">
        <div class="content-wrapper">
        <div class="card">
            <div class="card-header">
                <div class="float-start">
                    List Tasks 
                    @if(isset($projectTitle) && !empty($projectTitle))
                    {{ ' for project - '. $projectTitle }}
                    @endif
                </div>
            </div>
            <nav class="p-1">
                  <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="{{ route('tasks.manage_projects.filter', 'active') }}">Active Projects</a></li>
                       @if(isset($projectTitle) && !empty($projectTitle))
                        <li class="breadcrumb-item active"> {{ $projectTitle }}</li>
                      @endif
                      
                  </ol>
              </nav>
                    <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('success') }}
                        </div>
                    @endif
                         <br/>
                         <div id="success-message" class="alert alert-success"  role="alert"  style="display: none;"></div>
                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                        <table  id="tasksTable" class="display table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sno</th>
                                        <th>Employee Name</th>
                                        <th>Individual Task Name</th>
                                        <th>Employee Task Status</th>
                                    </tr>
                                </thead>
                              <tbody>
                              
                            </tbody>
                          </table>
                    </div>
                </div>
          </div> 
          <script type="text/javascript">
// Ensure jQuery is ready
$(document).ready(function () {
    $('#tasksTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tasks.active_projects_tasks', ['proj_id' => $proj_id]) }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'emp_name', name: 'emp_name' },
                { data: 'emp_task_name', name: 'emp_task_name' },
                { data: 'task_status', name: 'task_status' },
            ],
             pageLength: 10,
        });
});
            </script>
          @endsection        