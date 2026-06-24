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
                    List Team Types
                </div>
                @if (\App\Helpers\PermissionHelper::checkPermission('global.categories', config('global_permissions.Add')))
                    <div class="float-end">
                        <button type="button" id="addButton" class="btn btn-primary btn-sm">+Add New Team Type</button>
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

                            <table  id="projectsTable" class="display table table-bordered">
                                <thead>
                                <tr>
                                    <th>S.no</th>
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
                
            <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content logic_cont">
                        <div class="modal-header">
                            <h5 class="modal-title" id="dataModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        <div id="success-message1" class="alert alert-success"  role="alert"  style="display: none;"></div>
                        <div id="error-message1" class="alert alert-danger" style="display: none;"></div>

                            <form id="dataForm">
                                @csrf
                                <input type="hidden" id="recordId" name="id">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Team Type Name</label>
                                    <input type="text" class="form-control" id="team_typ_name" name="team_typ_name" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed" required>
                                </div>
                                 <div class="mb-3">
                                    <label for="name" class="form-label">Select Project Manager:</label>
                                    <select name="pm_id" id="pm_id" class="frm_ctrl_select">
                                        <option value="">Select</option>
                                        @foreach ($pms as $indpm => $val )
                                        <option value="{{ $indpm}}">{{ $val }} </option>
                                        @endforeach
                                    </select>

                                </div>
                                  <div class="col-md-4 mb-3" >
                                    <label for="name" class="form-label">Team Colour</label>
                                    <input type="color" id="team_color" name="team_color" value="#224E77" required>   
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
<script type="text/javascript">
// Ensure jQuery is ready
$(document).ready(function () {
    $('#projectsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('teams.team_types') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'team_typ_name', name: 'team_typ_name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                 pageLength: 10,
            });
// Open Modal for Adding a New Record
$('#addButton').click(function () {
  const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
      backdrop: 'static',
      keyboard: false
  });
  myModal.show();
  $('#dataModalLabel').text('Add New Team Type');
  $('#dataForm').trigger('reset');
  $('#recordId').val('');
});

// Open Modal for Editing a Record
$(document).off('click', '.editButton').on('click', '.editButton', function () {
  const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
      backdrop: 'static',
      keyboard: false
  });
  myModal.show();
  const id = $(this).data('id');

  // Fetch data for the selected record
  $.get('teams/edit_team_type/' + id , function (data) {
    

      $('#dataModalLabel').text('Edit Team Type');
      $('#recordId').val(data.id);
      $('#team_typ_name').val(data.team_typ_name);
      $('#pm_id').val(data.pm_id);
      $('#team_color').val(data.team_color);
      
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
            team_typ_name: {
                required: true,
                pattern: "^[a-zA-Z0-9\\s]*",
            },
            pm_id:{ required : true }

        },
        messages: {
            team_typ_name: {
                required: "Team Type Name is required.",
                pattern: "Special characters are not allowed in the category name.",
            },
            pm_id:{ required : "Select Project Manager for the Team Type." }
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
            const url = $('#recordId').val() ? '/update_team_type/' + $('#recordId').val() : '/store_team_type';
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
                        location.reload();
                    }, 5000); // Delay by 500ms to allow the modal to close
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
const url = '/teams/' + id + '/destroy_team_type';

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
            }, 5000);

      }
  });
  myModal.hide();
});
});
});
</script>
          @endsection
         