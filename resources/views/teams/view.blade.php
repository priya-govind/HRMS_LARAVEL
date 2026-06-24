@extends('layouts.app')
@section('content')
        @php
    use App\Helpers\PermissionHelper;
@endphp      
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
                    List Teams
                </div>
                 @if (PermissionHelper::checkPermission('global.categories', config('global_permissions.Add')))
                <div class="float-end">
                <button type="button" id="addButton" class="btn btn-primary btn-sm">+Add New Team</button>
                </div>
                @endif
            </div>
                    <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('success') }}
                        </div>
                    @endif
                    <div id="success-message" class="alert alert-success"  role="alert"  style="display: none;"></div>
                    <div id="error-message" class="alert alert-danger" style="display: none;"></div>

                            <table  id="teamTable" class="display table table-bordered">
                                <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Team Name</th>
                                    <th>Project Type </th>
                                    <th>Team Type</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                               
                                </tbody>
                        </table><br/>
                       
                    </div>
                </div>
          </div>
            <!-- Modal -->
                
            <div class="modal fade " id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
                <div class="modal-dialog assign_popup">
                    <div class="modal-content logic_cont">
                        <div class="modal-header">
                            <h5 class="modal-title" id="dataModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="teams">
                        <div id="success-message1" class="alert alert-success"  role="alert"  style="display: none;"></div>
                        <div id="error-message1" class="alert alert-danger" style="display: none;"></div>

                        <form id="dataForm" method="post">
                                @csrf
                                <input type="hidden" id="recordId" name="id">
                                <div class="row">
                                        <div class="col-md-5 mb-3">
                                    <label for="name" class="form-label">Team Name</label>
                                    <input type="text" class="form-control" id="team_name" name="team_name" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed" required>
                                </div>
                                </div>
                                     <div class="row">
                                        <div class="col-md-5 mb-3">
                                                <label for="name" class="form-label">Project Type</label>
                                                <select name="proj_type" id="proj_type" class="frm_ctrl_select" style="width:90% !important;">
                                                <option value="">Select </option>
                                                @foreach ($proj_types as $proj_typ )
                                                <option value="{{ $proj_typ->id }}">{{ $proj_typ->proj_typ_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-7 mb-3">
                                                <label for="name" class="form-label">Team Type</label><br/>
                                                <select name="team_type" id="team_type" class="frm_ctrl_select"
                                                @if (in_array(session('role_id'), config('global.task_monitor_roles')))
                                                    style="pointer-events: none;width:76% !important;"
                                                @else
                                                 style="width:76% !important;"
                                                @endif
                                                >
                                                <option value="">Select </option>
                                                @foreach ($team_types as $team_typ )
                                                <option value="{{ $team_typ->id }}">{{ $team_typ->team_typ_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                     </div>
                                    
                   
                                <div class="mb-3">
                                    <label for="name" class="form-label">Team Members</label>
                                    <select name="emp_id[]" id="emp_id" class="frm_ctrl_select multiple_drop_down"> 
                                        <option value="">Select</option>
                                    </select>
                                </div>                                
                                <div class="mb-3"
                                   @if (in_array(session('role_id'), config('global.task_monitor_roles')))
                                    style="display:none;"
                                   @endif
                                   >
                                    <label for="name" class="form-label">Control Team</label>
                                       <div class="checkbox-container">
                                           
                                            @foreach ($control_team as $emp )
                                                <label class="checkbox-item"> <input type="checkbox" class="roleCheckbox" name="ctrl_id[]" value="{{$emp->id}}">
                                                {{$emp->name }}</label>
                                            @endforeach   
                                            
                                       </div>
                                </div>

                                <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                            </form>
                        </div>
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
 <div class="modal fade" id="teamsModal" tabindex="-1" aria-labelledby="teamsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
         <h5 class="modal-title" id="confirmationModalLabel">Team Members</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="list_teams">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
// Ensure jQuery is ready
$(document).ready(function () {
    $('#emp_id').attr('style','pointer-events:none;').css('background-color:#ccc');
    $('#teamTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('teams.list_teams') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'team_name', name: 'team_name' },
                    {  data: 'proj_typ_name', name: 'proj_typ_name' },
                    {  data: 'type_name', name: 'type_name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                 pageLength: 10,
            });
$('#team_type').change(function() {
    var type = $(this).val();
    if(type) {
        //load team members
     var url='/get_teams_members_assign';
     var ctrl_url='/get_team_ctrl_members';
     var LoaderClass='.checkbox-container';
     var method='GET';
     var assign_id='#emp_id';
     var additionalData = {
                            team_types: type,
                        };
    ajaxload(url,method,assign_id,additionalData);
    ajaxLoadSelectDropdown_Checkboxes(ctrl_url,method,LoaderClass,additionalData);    
    } else {
        $('#emp_id').empty();
        $('#emp_id').append('<option value="">Select Team Members</option>');
    }
});
// Open Modal for Adding a New Record
$('#addButton').click(function () {
  const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
      backdrop: 'static',
      keyboard: false
  });
  myModal.show();
  $('#dataModalLabel').text('Add New Team');
  $('#dataForm').trigger('reset');
  $('#recordId').val('');
});
$(document).off('click', '.editButton').on('click', '.editButton', function () {
    const id = $(this).data('id');
    const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
    $.get(`teams/edit_team/${id}`)
        .done(function (data) {
            $('#dataModalLabel').text('Edit Team');
            $('#recordId').val(data.id);
            $('#team_name').val(data.team_name);
            $('#team_type').val(data.team_type || '');
            $('#proj_type').val(data.proj_type || '');
            const teamType = data.team_type;
            const TeamMembers = Array.isArray(data.selected_members) ? data.selected_members : [];
            // Load control member checkboxes
            ajaxLoadSelectDropdown_Checkboxes(
                            '/get_team_ctrl_members',
                            'GET',
                            '.checkbox-container',
                            { team_types: data.team_type },
                            data.ctrl_members,
                            'ctrl_id' // dynamic key name
                        );
            // Load team member assignments
            ajaxload(
                '/get_teams_members_assign',
                'GET',
                '#emp_id',
                { team_types: teamType },
                TeamMembers
            );
            // Pre-check control member checkboxes
            TeamMembers.forEach(memberId => {
                $(`input[name="emp_id[]"][value="${memberId}"]`).prop('checked', true);
            });
        })
        .fail(function (xhr, status, error) {
            console.error("Failed to fetch team data:", error);
        });
});
$(document).off('click', '.TeamsButton').on('click', '.TeamsButton', function () {
    const id = $(this).data('id');
    const myModal = new bootstrap.Modal(document.getElementById('teamsModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
    $.get(`teams/members/${id}`)
        .done(function (data) {
            let html = "<h6>Team Members</h6><ul>";
            data.team_members.forEach(member => {
                html += `<li>${member.name}</li>`;
            });
            html += "</ul>";

            html += "<h6>Reporting Members</h6><ul>";
            data.reporting_members.forEach(member => {
                html += `<li>${member.name}</li>`;
            });
            html += "</ul>";

            $('#list_teams').html(html);
        })
        .fail(function (xhr, status, error) {
            console.error("Failed to fetch team data:", error);
        });
});
// Handle Form Submission with Validation
$(document).ready(function () {
    // Add custom validator for 'pattern'
    $.validator.addMethod(
        "pattern",
        function (value, element, param) {
            return this.optional(element) || new RegExp(param).test(value);
        },
        "Invalid format."
    );

    // Initialize validation
    $("#dataForm").validate({
        errorClass: "is-invalid", // Add Bootstrap's 'is-invalid' class to highlight errors
        rules: {
            team_name: {
                required: true,
                pattern: "^[a-zA-Z0-9\\s]*",
            },
            proj_type:{
              required: true,  
            },
            team_type: {
                required: true,
            },
            "emp_id[]": {
                required: true,
            },
            "ctrl_id[]":{
                required: true,
            }

        },
        messages: {
            team_name: {
                required: "Team Name is required.",
                pattern: "Special characters are not allowed in the Team name.",
            },
            proj_type:{
              required: "Project Type is required.",  
            },
            team_type: {
                required: "Team Type is required.",
            },
            "emp_id[]": {
                required: "Select Team Members.",
            },
            "ctrl_id[]": {
                required: "Select Control Team Members.",
            },
           
        },
        highlight: function (element, errorClass) {
            $(element).addClass(errorClass); // Highlight invalid fields
        },
        unhighlight: function (element, errorClass) {
            $(element).removeClass(errorClass); // Remove highlight from valid fields
        },
        errorPlacement: function (error, element) {
            if (element.is(":checkbox")) {
                $(".checkbox-container").next(".is-invalid").remove(); // Remove existing error message
                $(".checkbox-container").after('<label class="is-invalid" for="team_type">'+ error.text() + '</label>'); // Append error after container
            } else {
                error.insertAfter(element); // Default behavior for other elements
            }
        },
        submitHandler: function (form) {
            const formData = $(form).serialize();
            const url = $('#recordId').val() ? '/update_team/' + $('#recordId').val() : '/store_team';
            const method = $('#recordId').val() ? 'PUT' : 'POST';
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
                        $('#teamTable').DataTable().ajax.reload(null, false);
                       // location.reload();
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
// Handle Form Submission for Add/Edit


// Confirmation Alert Box before Deleting
$(document).off('click', '.delete-btn') // Remove any existing handlers
.on('click', '.delete-btn', function () {

const id = $(this).data('id');
const url = '/teams/' + id + '/destroy_team';

const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
    // Add click listener for the Delete button inside the modal
    $('#confirmDelete').off('click').on('click', function () {
        
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
                location.reload();
            }, 1000);


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
  myModal.hide();
});
});
});
</script>
          @endsection
         