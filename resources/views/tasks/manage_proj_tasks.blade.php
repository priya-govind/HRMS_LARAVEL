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
                </div>
               
@can('PagePermit', ['global.categories', config('global_permissions.Add')])
                <div class="float-end">
                    <a href="{{ route('tasks.add_proj_tasks')}}"   class="btn btn-primary btn-sm">+Add New Task </a>
                </div>
@endcan
            </div>
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
                       
                         <form method="POST" id="TasksReport">
                                @csrf
                       <h4>Filter Tasks and Export:</h4><br/>
                      
                            <div class="row mb-3" style="display:none;">
                                <div class="col-md-2">
                                    <input type="radio" name="status_type" value="1" {{ $status['self'] }}> Your Tasks
                                </div>
                                <div class="col-md-2">
                                    <input type="radio" name="status_type" value="2" {{ $status['other'] }}> Employee's Tasks
                                </div>
                            </div>
                                <div class="row">
                                <div class="form-group col-md-1">
                                    <label>From Date</label>
                                <input type="text" class="form-control" id="start_date" name="start_date" placeholder="From Date" value="{{ request('start_date') }}"  readonly>
                                </div>
                                <div class="form-group col-md-1">
                                    <label>To Date</label>
                                    <input type="text" class="form-control" id="end_date" name="end_date" placeholder="To Date" value="{{ request('end_date') }}"  readonly>
                                </div>
                                <div class="form-group col-md-2" id="project_filter">
                                    <label>Project</label>
                                    <select name="project_id" id="project_id" class="form-control">
                                    <option value="">Select Project</option>
                                    @foreach ($projects as $proj)
                                    <option value="{{ $proj->id }}">{{ $proj->proj_name }}</option>
                                    @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2" id=module_filter">
                                    <label>Module</label>
                                    <select name="module_id" id="module_id" class="form-control">
                                    <option value="">Select Module</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2" id="employee_filter"    @if(!in_array(session('role_id'), config('global.restriction_free_roles'))) style="display:none;"  @endif>
                                    <label>Employee</label>
                                    <select name="emp_id" id="emp_id" class="form-control">
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} - {{ $emp->roles->first()->role_name }}</option>
                                    @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2" id="employee_filter" style="margin: 23px 0 0 0;">
                                    <button type="submit" class="btn btn-primary w-100">Generate</button>
                                </div>
                                    <div class="form-group col-md-2" id="download_tasks" style="margin: 23px 0 0 0;">
                                    <button class="btn btn-primary download_tasks">Export to excel 
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                    </button>
                                    </div>
                                </div>
              </form>
               <div class="float-end">
                            Hide Columns: 
                            <input type="checkbox" name="desc_visible" id="desc_visible"> Task Description
                            <input type="checkbox" name="comments_visible" id="comments_visible"> Comments<br/>
                        </div>
                        <table  id="tasksTable" class="display table table-bordered">
                                <thead>
                                    <tr>
                                       <th>Sno</th>
                                       <th>Project Name</th>
                                       <th>Module Name</th>
                                       <th>Task Name</th>
                                       <th>Task Desc</th>
                                       <th>Assigned to </th>
                                       <th>Deadline</th>
                                       <th>Over All Task Status</th>
                                        <th>Emp Task Status</th>
                                        <th>Emp Comments</th>
                                        <th>Assigned By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                              <tbody>
                              
                            </tbody>
                          </table>
                    </div>
                  
                               
                  
                </div>
          </div>
  <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this item?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="TaskModal" tabindex="-1" aria-labelledby="TaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content logic_cont">
            <div class="modal-header">
                <h5 class="modal-title" id="TaskModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="success-message2" class="alert alert-success" role="alert" style="display: none;"></div>
                <div id="error-message2" class="alert alert-danger" style="display: none;"></div>
                    
                <form id="TaskForm">
                    @csrf
                    <!-- method="post" action="{{ route('task_ind_update')}}" @method('PUT') -->
                    <input type="hidden" id="recordId" name="id">
                    <input type="hidden" name="task_id" id="taskid">
                    <div class="mb-3">
                        <label for="name" class="form-label">Task Name</label>
                        <input type="text" class="form-control" id="task_name" name="task_name">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Task Status:</label>&nbsp;&nbsp;
                        <select name="emp_proj_status" id="emp_proj_status" class="frm_ctrl_select">
                          <option  value="">Select</option>
                          @foreach ($task_status as $status)
                          <option value="{{ $status->id }}">{{ $status->proj_status_name }}</option>
                          @endforeach
                        </select>
                    </div>
                       <div class="mb-3">
                        <label for="name" class="form-label">Comments</label>
                       <textarea name="comments" id="comments1"  class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="TaskStatusModal" tabindex="-1" aria-labelledby="TaskStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog assign_popup">
        <div class="modal-content logic_cont">
            <div class="modal-header">
                <h5 class="modal-title" id="TaskStatusModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="success-messageStat" class="alert alert-success" role="alert" style="display: none;"></div>
                <div id="error-messageStat" class="alert alert-danger" style="display: none;"></div>
                <form id="editTaskStatus">
                        @csrf
                        @method('PUT')
                    <!-- method="post" action="{{ route('task_update_pm_status');}}" -->
                        <input type="hidden" id="recordTaskMainId" name="recordTaskMainId">
                            <div class="mb-3">
                                <label for="name" class="form-label">Task Status</label>
                                <select name="task_status" id="task_status_main" class="frm_ctrl_select">
                                    <option value="">Select</option>
                                    @foreach($project_status as $proj_status)
                                    <option value="{{ $proj_status->id}}">{{ $proj_status->proj_status_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="reopen_tag" style="display:none;">
                            <div class="mb-3">
                                <label for="name" class="form-label">Re open Type</label>
                               <input type="radio" name="reopen_type" value="1" class="form-check-input m-1 reopen_type">Assign For All
                               <input type="radio" name="reopen_type" value="2" class="form-check-input m-1 reopen_type">Assign For selected members
                                </textarea>
                            </div> 
                               <div class="mb-3" id="status_emp_div" style="display:none;">
                                <label for="name" class="form-label">Task Assigned Employees:</label>
                               <select name="status_emp[]" id="status_emp" class="form-control frm_ctrl_multiselect" multiple>

                               </select>
                                </textarea>
                            </div>
                            </div>
                             <div class="mb-3">
                                <label for="name" class="form-label">Comments</label>
                               <textarea name="comments" id="task_comments" class="form-control" style="width: 60%;height:auto;">
                                </textarea>
                            </div>
                                  
                        <button type="submit" class="btn btn-primary" id="saveBtnTask">Save</button>
                </form>
            </div>
        </div>
    </div>
</div> 
<div class="modal fade" id="ProjTaskModal" tabindex="-1" aria-labelledby="ProjTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content logic_cont">
            <div class="modal-header">
                <h5 class="modal-title" id="ProjTaskModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                <form id="dataForm" >
                    @csrf
                    <!-- @method('PUT') method="post" action="{{ route('task_ind_update') }}" -->
                    <input type="hidden" id="recordId" name="id">
                    <input type="hidden" name="task_id" id="task_id">
                    <div class="mb-3">
                        <label for="name" class="form-label">Task Name</label>
                        <input type="text" class="form-control" id="common_task_name" name="task_name" readonly>
                    </div>
                    <div class="mb-3" id="present_status" style="display: none;">
                        <label for="name" class="form-label">Present Task Status:</label>&nbsp;&nbsp;
                        <span></span>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Change Task Status:</label>&nbsp;&nbsp;
                        <select name="indiv_proj_status" id="indiv_proj_status" class="frm_ctrl_select">
                          <option  value="">Select</option>
                          @foreach ($project_status as $status)
                          <option value="{{ $status->id }}">{{ $status->proj_status_name }}</option>
                          @endforeach
                        </select>
                    </div>
                       <div class="mb-3">
                        <label for="name" class="form-label">Comments</label>
                       <textarea name="comments" id="comments"  class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>  
<div class="modal fade" id="ViewTeamMembers" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog view_indiv_popup">
        <div class="modal-content logic_cont" style="width: 112%;">
            <div class="modal-header">
                <h5 class="modal-title" id="ViewTeamMembersLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
               
                        <input type="hidden" id="recordTaskId" name="recordTaskId">
                    
                       <div id="mems_table">
                           <b> Team Members:</b>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="memberTaskRows">
        
                                </tbody></table>
                       </div>
                       <div id="docs_table">
                                <table class="table" id="docs_table">
                                <tbody id="TaskDocs">
        
                                </tbody>
                            </table>
                       </div>
            </div>
             <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        
      </div>
        </div>
    </div>
</div> 
          <script type="text/javascript">

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
// Ensure jQuery is ready
$(document).ready(function () {
     $('#download_tasks').hide();
   var table = $('#tasksTable').DataTable({
            processing: true,
            serverSide: true,
              ajax: {
                url: "{{ route('tasks.proj_tasks') }}",
                type: "POST",
                    data: function (d) {
                    d._token = "{{ csrf_token() }}";
                    d.status_type = $("input[name='status_type']:checked").val();
                    d.project_id = $('#project_id').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val(); 
                    if (d.status_type == "2") {
                        d.emp_id = $('#emp_id').val();
                    }
                    }
                },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'project', name: 'project', className: 'wrap-text'},
                { data: 'module_name', name: 'module_name' },
                { data: 'task_name', name: 'task_name', className: 'wrap-text' },
                { data: 'task_desc', name: 'task_desc', className: 'wrap-text'},
                { data: 'task_assigned_emps', name: 'task_assigned_emps' },
                { data: 'deadline', name: 'deadline' },
                { data: 'overall_task_status', name: 'overall_task_status', className: 'wrap-text' },
                { data: 'task_status', name: 'task_status' },
                { data: 'task_comments', name: 'task_comments', className: 'wrap-text'},
                { data: 'task_created_by', name: 'task_created_by' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
             pageLength: 10,
            rowCallback: function(row, data) {
                if (!data.endDate) return;
                let endDate = new Date(data.endDate);
                let today = new Date();
                let nextWeek = new Date(today);
                nextWeek.setDate(today.getDate() + 7);

                if (!isNaN(endDate.getTime()) && endDate >= today && endDate <= nextWeek) {
                    $(row).addClass("highlight-red_ajax");
                }
            }
        });
            @if(!in_array(session('role_id'), config('global.restriction_free_roles')))
                    table.column(5).visible(false); 
                @endif

$('#saveBtnTask').on('click', function(e) {
        e.preventDefault();
         if ($("#task_comments").val().trim() === "") {
                        $("#task_comments").addClass('is-invalid'); 
                        $(".error-label").remove();
                         $("#task_comments").after('<label class="error-label is-invalid">Enter Your Comments.</label>');
                         return false;
                        
                    } else {
                         $("#task_comments").removeClass('is-invalid'); 
                    }
          const formData = $('#editTaskStatus').serialize();
          const url = '/task_update_pm_status';
          const method = 'PUT';
            const myModal = bootstrap.Modal.getInstance(document.getElementById('TaskStatusModal'));
                $.ajax({
                url: url,
                type: method,
                data: formData,
                beforeSend: function () {
                    $('#saveBtnTask').prop('disabled', true).text('Saving...');
                },
                success: function (response) {
                    //alert(JSON.stringify(response));
                    $('#success-messageStat').text(response.success).fadeIn().delay(500).fadeOut();
                   
                    // Delay page reload
                    setTimeout(function () {
                        myModal.hide();
                        var table = $('#tasksTable').DataTable();
                       table.ajax.reload(null, false);

                    }, 500); // Delay by 500ms to allow the modal to close
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    $('#error-messageStat').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
                },
                complete: function () {
                    $('#saveBtnTask').prop('disabled', false).text('Save');
                },
            });  
 });
        $('#task_status_main').on('change', function() {
            if ($(this).val() === '14') {
                $('#reopen_tag').show();
                   if ($("#task_comments").val().trim() === "") {
                        $("#task_comments").addClass('is-invalid'); 
                        $(".error-label").remove();
                         $("#task_comments").after('<label class="error-label is-invalid">Enter Your Comments.</label>');
                        e.preventDefault(); 
                    } else {
                         $("#task_comments").removeClass('is-invalid');
                        
                         e.preventDefault(); 
                    }
                
            } else {
                $('#reopen_tag').hide(); // Hide if not 'Re open'
                 $("#task_comments").removeClass('is-invalid');
            }
        });

        $(document).off('click', '.reopen_type').on('click', '.reopen_type', function () {
            const reopen_type=$(this).val();
            const TaskId=$('#recordTaskMainId').val();
            if(reopen_type==2){
                $('#status_emp_div').show();
                    $.ajax({
                        url: '/get_assigned_teams_members/'+TaskId,
                        type: 'GET',
                            success: function(data) {
                               $('#status_emp').empty();
                                $('#status_emp').append('<option value="">Select Team Members</option>');
                               $.each(data, function(index, member) {
                                    $('#status_emp').append(
                                        `<option value="${member.employee_id}--${member.team_id}">${member.employee_name} - ${member.team_name}</option>`
                                    );
                                });
                            }
                    });
            } else {
                $('#status_emp_div').hide();
            }

        });

        $(document).off('click', '#ChangeStat').on('click', '#ChangeStat', function () {
    const myModal = new bootstrap.Modal(document.getElementById('TaskModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
                $('#TaskForm').validate()
                const id = $(this).data('id');
                $('#TaskForm').trigger('reset');
                // Fetch data for the selected record
                $.get('/tasks/' + id + '/pl_status', function (data) {
                    $('#TaskModalLabel').text('Edit Task Info');
                    //$('#recordId').val(id);
                    $('#taskid').val(id);
                    $('#task_name').val(data.task_info);    
                    $('#comments1').val(data.comments);
                    $('#task_id').val(data.task_id);
                });

});
$('#addMemberBtn').on('click', function() {
    if ($('#new_members option:selected').length > 0) {
    $('#new_members option:selected').each(function() {
        let selectedMembers=$(this).val();
        let [memberId, team_id] = selectedMembers.split("--");

        const memberName = $(this).data('name');
        const taskInfo = $(this).data('task');
        const taskStatus = $(this).data('status');
        
        const team_name= $(this).data('team_name');
        const team_owner=$(this).data('team_owner');
        const readonlyAttr = (team_owner !== 'own') ? 'readonly' : '';

        if (memberId && $('#emp_' + memberId).length === 0) { // Prevent duplicates
            $("#new_member").append(`
                <tr id="emp_${memberId}">
                    <td><input type="hidden" name="assign_mem_id[]" value="${memberId}">
                    <input type="hidden" name="assign_team_id[${memberId}]" value="${team_id}">
                     ${memberName+ "-" + team_name}</td>
                    <td><input type="text" class="form_control" name="assign_mem_task[${memberId}]" 
                    ${readonlyAttr} value="${taskInfo}"></td>
                    <td>${taskStatus}</td>
                    <td><button type="button" class="btn btn-danger btn-sm delete-btn_task" data-memtype="new" data-id="${memberId}">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </td>
                </tr>
            `);
            updateIds(memberId); // Append to hiddenAssignedIds and remove from hiddennewAssignedIds
        }
    });
       $('#new_mem_header').show();
}
 
});

$(document).on('click', '.delete-btn_task', function() {
    const itemId = $(this).data('id');
    const url = '/tasks/' + itemId;   
    const memtype = $(this).data('memtype');
    const confirmModalEl = document.getElementById('confirmationModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl, {
        backdrop: 'static',
        keyboard: false
    });

    $(confirmModalEl).attr('style', 'z-index:1063 !important');
    $('.modal-backdrop').last().attr('style', 'z-index:1055 !important');

    confirmModal.show();

    $('#confirmDelete').off('click').on('click', function() {
        callajax(url, 'DELETE', {
            _token: '{{ csrf_token() }}',
            itemId: itemId,
        });
        confirmModal.hide();
    });
});

            // Initialize validation
    $("#TaskForm").validate({
        errorClass: "is-invalid", 
        rules: {
            task_name: {
                required: true,
            },
            emp_proj_status: {
                required: true,
            },
        },
        messages: {
            task_name: {
                required: "Task Name is Required.",
            },
            emp_proj_status: {
                required: "Task Status is Required.",
            },
        },
        highlight: function (element, errorClass) {
            $(element).addClass(errorClass); // Highlight invalid fields
        },
        unhighlight: function (element, errorClass) {
            $(element).removeClass(errorClass); // Remove highlight from valid fields
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent()); // Place error message next to the field
        },
        submitHandler: function (form) {
            const formData = $(form).serialize();
            const url = '/task_ind_update';
            const method = 'PUT';
            const myModal = bootstrap.Modal.getInstance(document.getElementById('TaskModal'));


            // Submit the form via AJAX
            $.ajax({
                url: url,
                type: method,
                data: formData,
                beforeSend: function () {
                    $('#saveBtn').prop('disabled', true).text('Saving...');
                },
                success: function (response) {
                   // alert(JSON.stringify(response));
                    $('#success-message2').text(response.message).fadeIn().delay(500).fadeOut();
                   
                    // Delay page reload
                    setTimeout(function () {
                        myModal.hide();
                        var table = $('#tasksTable').DataTable();
                       table.ajax.reload(null, false);

                    }, 500); // Delay by 500ms to allow the modal to close
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    $('#error-message2').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
                },
                complete: function () {
                    $('#saveBtn').prop('disabled', false).text('Save');
                },
            });
        },
    });
$(document).off('click', '#UpdateStat').on('click', '#UpdateStat', function () {
    const myModal = new bootstrap.Modal(document.getElementById('TaskStatusModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
                // $('#StatusForm').validate()
                const id = $(this).data('id');
                $('#StatusForm').trigger('reset');
                // Fetch data for the selected record
                $.get('/tasks/' + id + '/get_pm_tasks_info', function (data) {
                    const TaskInfo = data.task;
                    const ReopenTask = data.reopen_tsk;
                   
                    $('#TaskStatusModalLabel').text('Edit Over All Task Status');
                    $('#recordTaskMainId').val(id);
                    //alert(JSON.stringify(TaskInfo));
                    $('#task_status_main').val(TaskInfo.task_status);
                   /*** if(TaskInfo.task_status=='14'){
                        $('#reopen_tag').show();
                          var reopenType = ReopenTask.reopen_type;
                            // Select the radio button with the matching value
                            $('.reopen_type[value="' + reopenType + '"]').prop('checked', true);

                         if(ReopenTask.reopen_type==2){
                            $('#status_emp_div').show();
                                $.ajax({
                                    url: '/get_assigned_teams_members/' + id,
                                    type: 'GET',
                                        success: function(data) {
                                        $('#status_emp').empty();
                                            $('#status_emp').append('<option value="">Select Team Members</option>');
                                        $.each(data, function(index, member) {
                                                $('#status_emp').append(
                                                    `<option value="${member.employee_id}--${member.team_id}">${member.employee_name} - ${member.team_name}</option>`
                                                );
                                            });
                                         // Pre-select members based on emp_ids
                                            var selectedEmpIds = ReopenTask.emp_ids.split(',').map(id => id.trim()); // Convert emp_ids to array
                                            $('#status_emp option').each(function() {
                                                   var optionValue = $(this).val().split('--')[0].trim().toString();  // Extract emp_id before '--'
                                                   if (selectedEmpIds.includes(optionValue) && optionValue!='') {
                                                        $(this).prop('selected', true);
                                                    }
                                            });
                                        }
                                });
                         } 
                         
                    }***/
                    $('#task_comments').val(TaskInfo.comments);
                     if ($("#task_comments").val().trim() === "" && TaskInfo.comments==="") {
                        $("#task_comments").addClass('is-invalid'); 
                        $(".error-label").remove();
                         $("#task_comments").after('<label class="error-label is-invalid">Enter Your Comments.</label>');
                        e.preventDefault(); 
                    } else {
                         $("#task_comments").removeClass('is-invalid');
                    }
                });

});
$(document).on('click', '.delete-btn', function () {
    const itemId = $(this).data('id'); // Get item ID from the button
    const url = '/tasks/' + itemId + '/destroy';
    const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
    // Add click listener for the Delete button inside the modal
    $('#confirmDelete').off('click').on('click', function () {
        deleteItem(itemId,url); // Call your delete function
        myModal.hide();
      
    });
});
$('#saveBtnAssign').on('click', function(e) {
        e.preventDefault();
          const formData = $('#dataForm').serialize();
          const url = '/task_update_main';
          const method = 'PUT';
            const myModal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
                $.ajax({
                url: url,
                type: method,
                data: formData,
                beforeSend: function () {
                    $('#saveBtnAssign').prop('disabled', true).text('Saving...');
                },
                success: function (response) {
                    //alert(JSON.stringify(response));
                    $('#success-message1').text(response.success).fadeIn().delay(500).fadeOut();
                   
                    // Delay page reload
                    setTimeout(function () {
                        myModal.hide();
                        var table = $('#tasksTable').DataTable();
                       table.ajax.reload(null, false);

                    }, 500); // Delay by 500ms to allow the modal to close
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    $('#error-message1').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
                },
                complete: function () {
                    $('#saveBtnAssign').prop('disabled', false).text('Save');
                },
            });  
 });
// Example delete function
function deleteItem(id,url) {
    $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
        const messageContainer = $('#success-message'); // Select the container for the message
            messageContainer.text(response.message).show(); // Update and display the message

            // Hide the message after 5 seconds
            setTimeout(function () {
                messageContainer.hide();
                 var table = $('#tasksTable').DataTable();
                   table.ajax.reload(null, false);
            }, 2000);


         // // Refresh page
      },
      error: function (xhr) {
        const messageContainer = $('#error-message'); // Select the container for the error message
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            messageContainer.text('Error: ' + errorMessage).show(); // Update and display the error message

            // Hide the error message after 5 seconds
            setTimeout(function () {
                messageContainer.hide();
            }, 2000);

      }
            });
}

function callajax(url,method,data) {
     
    $.ajax({
                url: url,
                type: method,
                data: data,
                success: function (response) {
                    const messageContainer = $('#success-message1'); // Select the container for the message
                    messageContainer.text(response.message).show(); // Update and display the message
                  
            // Hide the message after 5 seconds
                setTimeout(function () {
                    messageContainer.hide();
                    //location.reload();
                }, 2000);
      },
      error: function (xhr) {
        const messageContainer = $('#error-message1'); // Select the container for the error message
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            messageContainer.text('Error: ' + errorMessage).show(); // Update and display the error message
            // Hide the error message after 5 seconds
            setTimeout(function () {
                messageContainer.hide();
            }, 2000);

      }
            });
}
$('#desc_visible').on('click',function(){
    var isChecked = $("#desc_visible").prop("checked");
     var table = $('#tasksTable').DataTable() ;
        if(isChecked){
            table.column(4).visible(false);   
        } else {
            table.column(4).visible(true);   
        }
});
$('#comments_visible').on('click',function(){
    var isChecked = $("#comments_visible").prop("checked");
     var table = $('#tasksTable').DataTable() ;
        if(isChecked){
            table.column(8).visible(false);   
        } else {
            table.column(8).visible(true);   
        }
});

$(document).off('click', '#EditProjStat').on('click', '#EditProjStat', function () {
    const myModal = new bootstrap.Modal(document.getElementById('ProjTaskModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
        $('#dataForm').validate()
    const id = $(this).data('id');
    $('#dataForm').trigger('reset');
    // Fetch data for the selected record
    $.get('/tasks/' + id + '/user_proj_status', function (data) {
        $('#ProjTaskModalLabel').text('Edit Task Status');
        $('#recordId').val(data.id);
        $('#common_task_name').val(data.task.task_name);
             if( data.emp_task_status==14){
                        $('#present_status').show();
                        $('#present_status span').html(data.emp_status.proj_status_name);
                         $('#indiv_proj_status').val('');
                    } else {
                        $('#present_status').hide();
                         $('#indiv_proj_status').val(data.emp_task_status);
                    }
        $('#comments').val(data.emp_comments);
        $('#task_id').val(data.task_id);
    });
});
$(document).off('click', '#ViewProjStat').on('click', '#ViewProjStat', function () {
                const myModal1 = new bootstrap.Modal(document.getElementById('ViewTeamMembers'), {
                    backdrop: 'static',
                    keyboard: false
                });
                myModal1.show();
                
                const id = $(this).data('id');
              //  $('#dataForm').trigger('reset');
                $('#recordTaskId').val(id);
               
                       $("#memberTaskRows").empty(); // Clear previous entries
                       $('#ViewTeamMembersLabel').text('Task Related Information');
                        $.ajax({
                            url: `/tasks/${id}/get-assigned-members-info-tasks/`,
                            type: "GET",
                            success: function(response1) {
                              if (response1.tasks.length > 0) {
                                $('#mems_table').show();
                                $("#memberTaskRows").empty();
                                response1.tasks.forEach(function(members) {
                                    $("#memberTaskRows").append(`
                                        <tr>
                                            <td><input type="hidden" name="assign_mem_id[]" value="${members.id}"> ${members.employee.name}</td>
                                             <td>${members.emp_status.proj_status_name}</td>
                                        </tr>
                                    `);
                                });
                            } else {
                                $('#mems_table').hide();
                                 $("#memberTaskRows").append(`
                                        <tr>
                                            <td colspan="3" align="center">No Members Assigned.</td>
                                        </tr>
                                    `);
                            }
                             if (response1.attachments.length > 0) {
                                 $('#docs_table').show();
                                  $("#TaskDocs").empty().append(`<b>Task Related Documents:</b><br/>`);
                                response1.attachments.forEach(function(docs) {
                                    $("#TaskDocs").append(`
                                        <tr>
                                             <td>${docs.original_name}</td>
                                            <td>
                                                <a href="/task_uploads/${docs.stored_name}" download="${docs.stored_name}" class="btn btn-primary btn-sm"><i class="fa-solid fa-download"></i>
                                                </a><br></td>
                                            
                                        </tr>
                                    `);
                                });
                             } else {
                                   $('#docs_table').hide();
                             }

                            
                                //    if (response1.length === 0) {
                                //         $("#memberTaskRows").append(`
                                //         <tr>
                                //             <td colspan="3"> No additionally  members assigned.</td>
                                //         </tr>
                                //     `);
                                //     }
                            myModal1.show();
                            
                            }
                        });
});
    $("#dataForm").validate({
        errorClass: "is-invalid", 
        rules: {
            indiv_proj_status: {
                required: true,
            },
        },
        messages: {
            indiv_proj_status: {
                required: "Task Status is required.",
            },
        },
        highlight: function (element, errorClass) {
            $(element).addClass(errorClass); // Highlight invalid fields
        },
        unhighlight: function (element, errorClass) {
            $(element).removeClass(errorClass); // Remove highlight from valid fields
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent()); // Place error message next to the field
        },
        submitHandler: function (form) {
            const formData = $(form).serialize();
            const url = '/task_ind_update_pm_tasks';
            const method = 'PUT';
            const myModal = bootstrap.Modal.getInstance(document.getElementById('ProjTaskModal'));
            // Submit the form via AJAX
            $.ajax({
                url: url,
                type: method,
                data: formData,
                beforeSend: function () {
                    $('#saveBtn').prop('disabled', true).text('Saving...');
                },
                success: function (response) {
                    $('#success-message1').text(response.message).fadeIn().delay(5000).fadeOut();
                   
                    // Delay page reload
                    setTimeout(function () {
                        myModal.hide();
                         var table = $('#tasksTable').DataTable();
                       table.ajax.reload(null, false);
                       
                    }, 500); // Delay by 500ms to allow the modal to close
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    $('#error-message1').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
                },
                complete: function () {
                    $('#saveBtn').prop('disabled', false).text('Save');
                },
            });
        },
    });
     $("#TasksReport").validate({
        errorClass: "is-invalid", 
        rules: {
            start_date: {
                required: true,
            },
            end_date: {
                required: true,
            },
        },
        messages: {
            start_date: {
                required: "Start Date is Required.",
            },
            end_date: {
                required: "End Date is Required.",
            },
        },
        highlight: function (element, errorClass) {
            $(element).addClass(errorClass); // Highlight invalid fields
        },
        unhighlight: function (element, errorClass) {
            $(element).removeClass(errorClass); // Remove highlight from valid fields
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent()); // Place error message next to the field
        },
        submitHandler: function (form) {
         table.ajax.reload(null, false);
         $('#download_tasks').show();
        },
    });
     $("input[name='status_type']").change(function () {
    const selected = $(this).val();
    if (selected == "2") {
      $('#employee_filter').show();
    } else {
      $('#employee_filter').hide();
      $('#emp_id').val('');
    }
  });
$('#download_tasks').on('click', function(e) {
    e.preventDefault(); // stop default button behavior

    // initialize validation rules if not already done
    $("#TasksReport").validate();

    // run validation check
    if ($("#TasksReport").valid()) {
        const start = $('#start_date').val();
        const end = $('#end_date').val();
        const project = $('#project_id').val();
        const emp_id = $('#emp_id').val();
        const status_type = $("input[name='status_type']:checked").val();
        const module_id = $('#module_id').val();

        const downloadUrl = `/export-tasks?start_date=${start}&end_date=${end}&project_id=${project}&emp_id=${emp_id}&status_type=${status_type}&module_id=${module_id}`;

        window.location.href = downloadUrl; // ✅ only runs if valid
    } else {
        // show a friendly message if invalid
        alert("Please fill all required fields before downloading.");
    }
});

    $('#project_id').change(function() {
              var proj_id = $(this).val();
              if(proj_id) {
                  $.ajax({
                      url: '/get_project_modules/' + proj_id,
                      type: 'GET',
                      success: function(data) {
                          $('#module_id').empty();
                          $('#module_id').append('<option value="">Select Module</option>');
                          $.each(data, function(id, name) {
                              $('#module_id').append('<option value="' + id + '">' + name + '</option>');
                          });
                           
                      }
                  });
              } else {
                  $('#module_id').empty();
                  $('#module_id').append('<option value="">Select Module</option>');
              }
        });
          $('#module_id').change(function() {
            var type = $(this).val();
                var module_id = $('#module_id').val();
                if(module_id) {
                    $.ajax({
                        url: '/get_assign_proj_members/' + module_id,
                        type: 'GET',
                            data: {
                              module_id: module_id,
                                },
                        success: function(data) {
                            $('#emp_id').empty();
                          // $('#emp_id').append('<option value="">Select Employees</option>');
                            $.each(data, function(id, name) {
                                $('#emp_id').append('<option value="' + id + '">' + name + '</option>');
                            });
                           // $('#emp_id').multiselect('reload');
                        }
                    });
                } else {
                    $('#emp_id').empty();
                    $('#emp_id').append('<option value="">Select Employee</option>');
                }
            });
  });
            </script>
          @endsection
         