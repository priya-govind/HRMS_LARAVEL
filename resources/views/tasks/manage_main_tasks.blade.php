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
               

                <div class="float-end">
                    <a href="{{ route('tasks.assign_tasks')}}"   class="btn btn-primary btn-sm">+Add New Task </a>
                </div>
           
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
                        <table  id="tasksTable" class="display table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sno</th>
                                        <th>Task Name</th>
                                        <th>Project Name</th>
                                        <th>Overall Task Status</th>
                                        <th>Team Status</th>
                                        <th>Created By</th>
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

<div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog assign_popup">
        <div class="modal-content logic_cont" style="width:150% !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="dataModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                
                <form id="dataForm" >
                    @csrf
                    @method('PUT')
                    <!-- method="post" action="{{ route('task_update_main');}}" -->
                        <input type="hidden" id="recordTaskId" name="recordTaskId">
                        <input type="hidden" id="hiddenAssignedIds" name="hiddenAssignedIds">
                        <input type="hidden" id="hiddennewAssignedIds" name="hiddennewAssignedIds">
                               <tr>
                             <td>   <div class="mb-3">
                        <label for="name" class="form-label"><h4> Reporting Members for the Task:</h4></label>
                       <span id="report_mem" style="font-weight:bold;"></span> 
                    </div><td>
                        </tr>
             <div class="assign-task-heading">
                        <h4>
                             Task Assigned Members
                        </h4>
             </div>
                     <table class="table">
                        <thead>
                            <tr id="old_header">
                                <th>Member</th>
                                <th>Task</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="memberTaskRows">
  
                        </tbody>
                         <tr id="no_members">
                            <td colspan="4"  class="blinking_txt">
                                <h5>No New Members to show.</h5>
                            </td> 
                         </tr>   
                        <tr>
                            
                        <td id="new_emp_sec" colspan='4'>
                        <div class="new-member-section">
                            <h4>Select New Members to Assign:</h4>
                        </div>
                       <select name="new_members[]" id="new_members" class="frm_ctrl_select new_mem_drop" multiple>
                            </select> 
                            <button type="button"  id="addMemberBtn"  class="btn btn-success btn-sm add_new">
                        <i class="fa fa-plus"></i>
                        </button>
                            </td>
                        </tr>
                       
                        
                        <tr id="new_mem_header" style="display:none;">
                                <th>Member</th>
                                <th>Task</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr> 
                    
                        <tbody id="new_member">
                             
                            </tbody>
                            
                        </table>                
                      
                    <div align="right" class="m-3">
                        <button type="submit" class="btn btn-primary" id="saveBtnAssign">Save</button>
                    </div>
                </form>
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
                    <!-- method="post" action="{{ route('task_update_main_status');}}" -->
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
          <script type="text/javascript">
// Ensure jQuery is ready
$(document).ready(function () {
    $('#tasksTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tasks.manage_tasks') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                { data: 'task_name', name: 'task_name' },
                { data: 'project.proj_name', name: 'project.proj_name' },
                { data: 'task_status', name: 'task_status' },
                { data: 'team_status', name: 'team_status' },
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
          const url = '/task_update_main_status';
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
                    $('#emp_proj_status').val(data.emp_task_status);
                    $('#comments1').val(data.comments);
                    $('#task_id').val(data.task_id);
                });

});
$(document).off('click', '#ViewStat').on('click', '#ViewStat', function () {
    // Initialize the Bootstrap modal
    const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static',
        keyboard: false
    });
    $('#dataModalLabel').text('Edit Task Info');

    // Clear previous entries before showing the modal
    $("#memberTaskRows").empty(); // Assigned members table
    $("#new_member").empty(); // Unassigned members section
    $("#new_members").empty().append(`<option value="">Select</option>`); // Clear dropdown and add default

    myModal.show();

    const taskId = $(this).data('id');
    $('#recordTaskId').val(taskId); // Set task ID

    // Fetch assigned & unassigned members via AJAX
    $.ajax({
        url: `/tasks/${taskId}/get-assigned-members-info/`,
        type: "GET",
        success: function(response) {
            const assignedMembers = response.members;
            const unassignedEmployees = response.unassign_Emp;

            console.log("Assigned Members:", assignedMembers);
            console.log("Unassigned Employees:", unassignedEmployees);

            // Populate assigned members in table
            if (assignedMembers.length === 0) {
                $('#old_header').hide();
            }
 var TeamStatus=response.team_status;
 
            assignedMembers.forEach(function(member) {
                var readonlyAttr = member.team_owner !== 'own' ? 'readonly="readonly"' : '';
                var Class=member.team_owner !== 'own' ? ' class="form_control disabledtext" ' : 'class="form_control"';
                var trClass = member.team_owner !== 'own' ? ' class="faded" ' : '  class="bright" ';
               
                $("#memberTaskRows").append(`
                    <tr id="emp_${member.pivot_id}" ${trClass}>
                        <td><input type="hidden" name="assign_mem_id[]" value="${member.employee_id}">
                        <input type="hidden" name="assign_team_id[${member.employee_id}]" value="${member.team_id}">
                        ${member.emp_name + "-" + member.team_name}</td>
                        <td><input type="text" ${Class} name="assign_mem_task[${member.employee_id}]" 
                        value="${member.task_info ? member.task_info : ''}" ${readonlyAttr}></td>
                        <td>${member.task_status}</td>
                        <td>
                            ${ 
                                (response.cntrl_teams.includes(member.team_id) && (member.task_status_id!=14 &&  member.task_status_id!=11))
                                ? `<button type="button" class="btn btn-danger btn-sm delete-btn_task" data-memtype="old" data-id="${member.pivot_id}">
                                    <i class="fa fa-trash-o"></i>
                                </button>`
                                : ''
                            }
                        </td>
                    </tr>
                `);
            });

            $('#hiddenAssignedIds').val(response.assignedIds);

            // Populate unassigned members dropdown
            $('#new_mem_header').hide();
            var unassignedIds = [];
        
            if (unassignedEmployees.length === 0) {
                $('#new_emp_sec').hide();
                $('#no_members').show();
            } else {
                $('#new_emp_sec').show();
                $('#no_members').hide();
            }

            unassignedEmployees.forEach(function(emp) {
                if (!unassignedIds.includes(emp.id)) {
                    unassignedIds.push(emp.id);
                }

                $("#new_members").append(`
                    <option value="${emp.id}--${emp.team_id}" data-name="${emp.name}" 
                    data-team_name="${emp.team_name}" data-task="" data-status="New" 
                    data-team_owner="${emp.team_owner}">${emp.name + "-" + emp.team_name}</option>
                `);
            });

            $('#hiddennewAssignedIds').val(unassignedIds.join(','));

            const tst = response.report_members.join(',');
            $('#report_mem').text(tst);
        },
        error: function(xhr, status, error) {
            console.error("Error fetching data:", error);
        }
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
    const url = '/tasks/remove_task_employee';
    const memtype=$(this).data('memtype');
    const confirmModalEl = document.getElementById('confirmationModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl, {
        backdrop: 'static',
        keyboard: false
    });

    // bump z-index above dataModal
    // $(confirmModalEl).css('z-index', 1063);
    // $('.modal-backdrop').last().css('z-index', 1055);
$(confirmModalEl).attr('style', 'z-index:1063 !important');
$('.modal-backdrop').last().attr('style', 'z-index:1055 !important');

    confirmModal.show();


    $('#confirmDelete').off('click').on('click', function() {
        callajax(url, 'DELETE', {
            _token: '{{ csrf_token() }}',
            itemId: itemId,
        }, );

        $('#emp_' + itemId).remove(); // Remove only the specific row
        $("#new_members option[value='" + itemId + "']").prop("selected", false);
        RollbackIds(itemId);
        if(memtype=='old'){
        // Fetch unassigned employees & reload BOTH dropdown and table
        const taskId = $('#recordTaskId').val();
       
        $.ajax({
            url: `/tasks/LoadUnassignedEmployees/${taskId}`,
            type: "GET",
            success: function(response) {
                const unassignedEmployees = response.unassign_Emp;

                // Clear & refresh dropdown
                $("#new_members").empty().append(`<option value="">Select</option>`);

                unassignedEmployees.forEach(function(emp) {
                    $("#new_members").append(`<option value="${emp.id}--${emp.team_id}" data-name="${emp.name}" data-team_id="${emp.team_id}" data-team_name="${emp.team_name}" data-task=" "  data-status="New"  data-team_owner="${emp.team_owner}">${emp.name + "-" + emp.team_name}
</option>`);
                });
                $('#new_emp_sec').show();
                $('#no_members').hide();
            }
        });
    } else{
        if($("#new_member tr").length==0){
            $('#new_mem_header').hide();
        }
    }
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
                $.get('/tasks/' + id + '/get_task_info', function (data) {
                    const TaskInfo = data.task;
                    const ReopenTask = data.reopen_tsk;
                   
                    $('#TaskStatusModalLabel').text('Edit Over All Task Status');
                    $('#recordTaskMainId').val(id);
                    $('#task_status_main').val(TaskInfo.team_status);
                    if(TaskInfo.team_status=='14'){
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
                    }
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
    const url = '/tasks/' + itemId + '/destroy_task';
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
function updateIds(id) {
    var assignedField = $('#hiddenAssignedIds');
    var newAssignedField = $('#hiddennewAssignedIds');

    // Convert input values to arrays
    var assignedValues = assignedField.val().split(',').filter(Boolean);
    var newAssignedValues = newAssignedField.val().split(',').filter(Boolean);

    // Append to hiddenAssignedIds if not already present
    if (!assignedValues.includes(id.toString())) {
        assignedValues.push(id);
    }

    // Remove from hiddennewAssignedIds
    newAssignedValues = newAssignedValues.filter(value => value !== id.toString());

    // Update fields
    assignedField.val(assignedValues.join(','));
    newAssignedField.val(newAssignedValues.join(','));
}

function RollbackIds(id) {
    var assignedField = $('#hiddenAssignedIds');
    var newAssignedField = $('#hiddennewAssignedIds');

    // Convert input values to arrays safely
    var assignedValues = assignedField.val() ? assignedField.val().split(',').filter(Boolean) : [];
    var newAssignedValues = newAssignedField.val() ? newAssignedField.val().split(',').filter(Boolean) : [];

    id = id.toString(); // Ensure ID is always a string

    // Add to newAssignedValues if not already present
    if (!newAssignedValues.includes(id)) {
        newAssignedValues.push(id);
    }

    // Remove from assignedValues
    assignedValues = assignedValues.filter(value => value !== id);

    // Update input fields
    assignedField.val(assignedValues.join(','));
    newAssignedField.val(newAssignedValues.join(','));
}

  });
            </script>
          @endsection
         