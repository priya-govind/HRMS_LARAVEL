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
        <div class="card">
            <div class="card-header">
                <div class="float-start">
                    List Tasks
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
                                   <th>Main Task Name </th>
                                   <th>Task Name </th>
                                  <th>Project Name </th>
                                  <th>Task Status</th>
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
<div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content logic_cont">
            <div class="modal-header">
                <h5 class="modal-title" id="dataModalLabel"></h5>
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
                        <input type="text" class="form-control" id="task_name" name="task_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Task Status:</label>&nbsp;&nbsp;
                        <select name="emp_proj_status" id="emp_proj_status" class="frm_ctrl_select">
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
<div class="modal fade" id="modalpopstat" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog view_indiv_popup">
        <div class="modal-content logic_cont" style="width: 112%;">
            <div class="modal-header">
                <h5 class="modal-title" id="modalpopstatLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
               
                        <input type="hidden" id="recordTaskId" name="recordTaskId">
                     <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Task</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="memberTaskRows">
  
                        </tbody>
                    </table>
            </div>
             <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        
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
                pageLength: 10, 
                 ajax: "{{ url()->full() }}",
                //ajax: "{{ route('tasks.manage_tasks') }}",
                columns: [
                     { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                     { data: 'main_task_name', name: 'main_task_name' },
                     { data: 'task_name', name: 'task_name' },
                    { data: 'project_name', name: 'project_name' },
                    { data: 'task_status', name: 'task_status' },
                    { data: 'task_created_by', name: 'task_created_by' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                 pageLength: 10,
                rowCallback: function(row, data) {
                    if (!data.endDate) return; // Ensure endDate exists
                    
                    let endDate = new Date(data.endDate.replace(" ", "T")); // Proper ISO format
                    let today = new Date();
                    let nextWeek = new Date();
                    nextWeek.setDate(today.getDate() + 7);

                    if (!isNaN(endDate.getTime())) { // Check valid date format
                        if (endDate >= today && endDate <= nextWeek) {
                            $('td', row).addClass("highlight-red_ajax"); // Add custom class
                                console.log("Class added to row:", row);
                        }
                    }
                }
            });
$(document).off('click', '#ViewStat').on('click', '#ViewStat', function () {
                const myModal1 = new bootstrap.Modal(document.getElementById('modalpopstat'), {
                    backdrop: 'static',
                    keyboard: false
                });
                myModal1.show();
                
                const id = $(this).data('id');
              //  $('#dataForm').trigger('reset');
                $('#recordTaskId').val(id);
               
                       $("#memberTaskRows").empty(); // Clear previous entries
                       $('#modalpopstatLabel').text('Task Members');
                        $.ajax({
                            url: `/tasks/${id}/get-assigned-members-info/`,
                            type: "GET",
                            success: function(response1) {
                               
                              if (response1.members && response1.members.length > 0) {
                                response1.members.forEach(function(members) {

                                    $("#memberTaskRows").append(`
                                        <tr>
                                            <td><input type="hidden" name="assign_mem_id[]" value="${members.id}"> ${members.emp_name + "-" + members.team_name}</td>
                                            <td>${members.task_info ? members.task_info : 'Not Assigned'}</td>
                                             <td>${members.task_status}</td>
                                        </tr>
                                    `);
                                });
                            } else {
                                 $("#memberTaskRows").append(`
                                        <tr>
                                            <td colspan="3" align="center">No Members Assigned.</td>
                                        </tr>
                                    `);
                            }
                                   if (response1.length === 0) {
                                        $("#memberTaskRows").append(`
                                        <tr>
                                            <td colspan="3"> No additionally  members assigned.</td>
                                        </tr>
                                    `);
                                    }
                            myModal1.show();
                            
                            }
                        });
});
            $('#dataModal').on('shown.bs.modal', function () {
                $('#dataForm').validate(); // Initialize validation once modal is fully loaded
            });

            $(document).off('click', '#EditStat').on('click', '#EditStat', function () {
                const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                myModal.show();
                 $('#dataForm').validate()
                const id = $(this).data('id');
                $('#dataForm').trigger('reset');
                // Fetch data for the selected record
                $.get('/tasks/' + id + '/user_status', function (data) {
                    $('#dataModalLabel').text('Edit Task Status');
                    $('#recordId').val(data.id);
                    $('#task_name').val(data.task_info);
                    $('#emp_proj_status').val(data.emp_task_status);
                    $('#comments').val(data.comments);
                    $('#task_id').val(data.task_id);
                });
            });
            // Initialize validation
    $("#dataForm").validate({
        errorClass: "is-invalid", 
        rules: {
            emp_proj_status: {
                required: true,
            },
        },
        messages: {
            emp_proj_status: {
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
            const url = '/task_ind_update';
            const method = 'PUT';
            const myModal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
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

  });
            </script>
          @endsection
         