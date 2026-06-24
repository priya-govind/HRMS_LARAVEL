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
                    List Modules of Project
                </div>
                 {{-- @can('PagePermit', [config('global_permissions.Add')]) --}}
                <div class="float-end">
                    <button type="button" id="addButton" class="btn btn-primary btn-sm">+Add New Project Module
                </div>
               {{-- @endcan --}}
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
                                  <th>Module Name</th>
                                  <th>Project Name </th>
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
                            <form id="dataForm" method="post" action="{{ route('modules.store')}}">
                                @csrf
                                <input type="hidden" id="recordId" name="id">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Module Name</label>
                                    <input type="text" class="form-control" id="module_name" name="module_name" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed" required>
                                </div>
                                 <div class="mb-3">
                                    <label for="name" class="form-label">Project Name</label>
                                    <select name="proj_id" id="proj_id"  class="frm_ctrl_select">
                                         @foreach ($projects as $proj)
                                               <option value="{{$proj->id}}">{{$proj->proj_name }}</option>
                                            @endforeach 
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Module Description</label>
                                    <textarea name="desc"  class="form-control" id="desc"></textarea>
                                </div>
                                     <div class="mb-3">
                                    <label for="name" class="form-label">Assign Employees</label>
                                       <div class="checkbox-container">
                                            @foreach ($employees as $emp )
                                                <label class="checkbox-item"> <input type="checkbox" class="roleCheckbox" name="emp_id[]" value="{{$emp->id}}">
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
 <div class="modal fade" id="projectsModal" tabindex="-1" aria-labelledby="projectsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
         <h5 class="modal-title" id="projectsModalLabel">Modules Asssigned</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="list_projects">
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
                $('#projectsTable').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: "{{ url()->full() }}", 
                            //ajax: "{{ route('tasks.manage_projects') }}",
                            columns: [
                                 { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                                { data: 'module_name', name: 'module_name' },
                                { data: 'proj_name', name: 'proj_name' },
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
                $('#dataModalLabel').text('Add New Project Module');
                $('#dataForm').trigger('reset');
                $('#recordId').val('');
            });
            $(document).off('click', '.editButton').on('click', '.editButton', function () {
                const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                myModal.show();
                const id = $(this).data('id');
                // Reset form before loading new values
                    $('#dataForm').trigger('reset');       
                    $('#recordId').val('');                
                    $('.roleCheckbox').prop('checked', false);
                // Fetch data for the selected record
                $.get('/modules/' + id + '/edit', function (data) {
                    $('#dataModalLabel').text('Edit Project Module');
                    $('#recordId').val(data.module.id);
                    $('#module_name').val(data.module.module_name);
                    $('#proj_id ').val(data.module.proj_id);
                    $('#desc').val(data.module.desc);
                    const TeamMembers = Array.isArray(data.module_assign) ? data.module_assign : [];
                    TeamMembers.forEach(memberId => {
                        $(`input[name="emp_id[]"][value="${memberId}"]`).prop('checked', true);
                    });
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
                        module_name: {
                                required: true,
                                pattern: "^[a-zA-Z0-9\\s]*",
                            },
                        proj_id: {
                                required: true,
                            },
                        desc:{
                            required: true,
                        },
                         "emp_id[]": {
                            required: true,
                        },
                    },
                    messages: {
                    module_name: {
                            required: "Module name is required.",
                            pattern: "Special characters are not allowed in the module name.",
                        },
                    proj_id: {
                            required: "Project Name is required.",
                        },
                     desc:{
                        required: "Enter Module Description.",
                    },
                    "emp_id[]": {
                        required: "Select Employees to Assign.",
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
                        const url = $('#recordId').val()
                                    ? '/modules/' + $('#recordId').val()
                                    : '/modules';
                        const method = $('#recordId').val() ? 'PUT' : 'POST';

                        const myModal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
                        // Submit the form via AJAX
                        $.ajax({
                            url: url,
                            type: method,
                            data: formData,
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            beforeSend: function () {
                                $('#saveBtn').prop('disabled', true).text('Saving...');
                            },
                            success: function (response) {
                                $('#success-message1').text(response.message).fadeIn().delay(5000).fadeOut();
                                setTimeout(function () {
                                    myModal.hide();
                                        let table = $('#projectsTable').DataTable();
                                    // table.search('').columns().search('').draw(); // clears global + column filters
                                        table.ajax.reload(null, false); // reloads data without resetting pagination
                                   
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
                const url = '/modules/' + itemId;
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
            $(document).off('click', '.ModulesButton').on('click', '.ModulesButton', function () {
                const id = $(this).data('id');
                const myModal = new bootstrap.Modal(document.getElementById('projectsModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                myModal.show();
                $.get(`employees/modules/${id}`)
                    .done(function (data) {
                        $('#projectsModalLabel').text('Modules Assigned Employees.');
                        let html = "";
                        if (data.length === 0) {
                                // No records case
                                html = "No Employees Assigned";
                            } else {
                            //      $.each(data, function(id, name) {
                            //       html += `<li>${name}</li>`;
                            //   });
                                Object.values(data).forEach(member => {
                                    html += `<li>${member}</li>`;
                                });
                                html += "</ul>";
                            }
                        $('#list_projects').html(html);
                    })
                    .fail(function (xhr, status, error) {
                        console.error("Failed to fetch team data:", error);
                    });
            });
            });
            </script>
          @endsection
         