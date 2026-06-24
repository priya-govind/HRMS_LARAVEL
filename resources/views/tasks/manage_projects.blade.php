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
                    List Projects
                </div>
                 @can('PagePermit', ['global.categories', config('global_permissions.Add')])
                <div class="float-end">
                    <button type="button" id="addButton" class="btn btn-primary btn-sm">+Add New Project
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
                        <table  id="projectsTable" class="display table table-bordered">
                              <thead>
                                <tr>
                                  <th>Sno</th>
                                  <th>Project Name </th>
                                  <th>Start Date</th>
                                  <th>End Date</th>
                                  <th>Project Status</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>
                            </tbody>
                          </table>
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
                                    <label for="name" class="form-label">Project Name</label>
                                    <input type="text" class="form-control" id="proj_name" name="proj_name" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed" required>
                                </div>
                                 <div class="mb-3">
                                    <label for="name" class="form-label">Description</label>
                                    <textarea name="proj_desc"  class="form-control" id="proj_desc"></textarea>
                                </div>
                                 <div class="row mb-3">
                                    <div class="col-md-6">
                                    <label for="name" class="form-label">Commencement Date</label>
                                    <input type="text" name="start_date"  class="form-control" id="start_date">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">End Date</label>
                                        <input type="text" name="end_date"  class="form-control" id="end_date">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Project Status</label>
                                    <select name="proj_status" id="proj_status" class="frm_ctrl_select">
                                    <option value="">Select </option>
                                    @foreach ($project_status as $proj_st )
                                      <option value="{{ $proj_st->id }}">{{ $proj_st->proj_status_name }}</option>
                                      @endforeach
                                    </select>
                                </div>
                                  <div class="col-md-4 mb-3" >
                                    <label for="name" class="form-label">Project Colour</label>
                                    <input type="color" id="proj_color" name="proj_color" value="#224E77" required>   
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
                            ajax: "{{ url()->full() }}", 
                            //ajax: "{{ route('tasks.manage_projects') }}",
                            columns: [
                                { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                                { data: 'proj_name', name: 'proj_name' },
                                { data: 'start_date', name: 'start_date'},
                                { data: 'end_date', name: 'end_date'},
                                { data: 'status.proj_status_name', name: 'status.proj_status_name' },
                                { data: 'action', name: 'action', orderable: false, searchable: false }
                            ]
                        });
                // Add and Edit Modal Handling
            $('#addButton').click(function () {
                const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                myModal.show();
                $('#dataModalLabel').text('Add New Project');
                $('#dataForm').trigger('reset');
                $('#recordId').val('');
            });
            $('#start_date,#end_date').datepicker({
                dateFormat: 'dd-mm-yy',
                dropdown: true,
                scrollbar: true
            });
            $(document).off('click', '.editButton').on('click', '.editButton', function () {
                const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                myModal.show();
                const id = $(this).data('id');

                // Fetch data for the selected record
                $.get('/tasks/' + id + '/edit_project', function (data) {
                    $('#dataModalLabel').text('Edit Project');
                    $('#recordId').val(data.id);
                    $('#proj_name').val(data.proj_name);
                    let formatted_st_dt = $.datepicker.formatDate('dd-mm-yy', new Date(data.start_date));
                        $('#start_date').val(formatted_st_dt);
                    let formatted_end_dt = $.datepicker.formatDate('dd-mm-yy', new Date(data.end_date));
                        $('#end_date').val(formatted_end_dt);
                    $('#proj_desc').val(data.proj_desc);
                    $('#proj_status').val(data.proj_status || '');
                    $('#proj_color').val(data.proj_color);
                });
            });
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
                    proj_name: {
                            required: true,
                            pattern: "^[a-zA-Z0-9\\s]*",
                        },
                    proj_desc: {
                            required: true,
                        },
                    start_date: {
                            required: true,
                        },
                    end_date: {
                            required: true,
                        },
                    proj_status: {
                            required: true,
                        },
                    },
                    messages: {
                    proj_name: {
                            required: "Project name is required.",
                            pattern: "Special characters are not allowed in the project name.",
                        },
                    proj_desc: {
                            required: "Project Description is required.",
                        },
                    start_date: {
                            required: "Commencement Date is required.",
                        },
                    end_date: {
                            required: "End Date is required.",
                        },
                        proj_status: {
                            required: "Please select a project status.",
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
                        const url = $('#recordId').val() ? '/update_project/' + $('#recordId').val() : '/tasks/store_projects';
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
                                setTimeout(function () {
                                    myModal.hide();
                                    if( $('#recordId').val()){
                                        let table = $('#projectsTable').DataTable();
                                    // table.search('').columns().search('').draw(); // clears global + column filters
                                        table.ajax.reload(null, false); // reloads data without resetting pagination
                                    } else {
                                        location.reload();
                                    }
                                }, 2000); // shorter delay just for modal close

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

                $(document).on('click', '.delete-btn', function () {
                const itemId = $(this).data('id'); // Get item ID from the button
                const url = '/tasks/' + itemId + '/destroy_project';
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
                            location.reload();
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
            });
            </script>
          @endsection
         