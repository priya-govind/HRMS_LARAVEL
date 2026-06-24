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
                    List Employees
                </div>
               

               @can('PagePermit', ['global.categories', config('global_permissions.Add')])
                    <div class="float-end">
                        <a href="{{ route('employees.create')}}"   class="btn btn-primary btn-sm">+Add New Employee </a>
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
                   <table id="userTable" class="display table table-bordered">

                    <thead>
                    <tr>
                        <th>S.no</th>
                        <th>Users</th>
                        <th>Role</th>
                          @if($edit_permit || $delete_permit)
                             <th>Action</th>
                          @endif
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
 <div class="modal fade" id="projectsModal" tabindex="-1" aria-labelledby="projectsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
         <h5 class="modal-title" id="projectsModalLabel">Projects Asssigned</h5>
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
  /*For no conflict* */
      //  var jq = $.noConflict();
      $(document).ready(function () {
            $(document).off('click', '.assignedInventory').on('click', '.assignedInventory', function () {
                 const id = $(this).data('id');
                const myModal = new bootstrap.Modal(document.getElementById('assignedInventoryModal'), {
                            backdrop: 'static',
                            keyboard: false
                        });
                $.ajax({
                url: '/manage_inventory/' + id + '/show_user_assets',   // replace with your API/route
                type: "GET",
                dataType: "json",
                success: function(response){
                    $('#assignedInventoryModalLabel').text('Assigned  Inventory for '+response.user_info);
                    // Build table
                    let table = "<table class='table table-bordered'>";
                    table += "<thead><tr><th>Item Name</th><th>Serial Number</th></tr></thead><tbody>";
                   if (response.items && response.items.length > 0) {
                        $.each(response.items, function(index, item){
                        table += "<tr>";
                        table += "<td>" + item.item_name + "</td>";
                        table += "<td>" + item.serial_no + "</td>";
                        table += "</tr>";
                        });
                    } else {
                        table += "<tr>";
                        table += "<td colspan='2' align='center'>No Assets Assigned.</td>";
                        table += "</tr>"; 
                    }


                    table += "</tbody></table>";

                    // Insert into modal body
                    $("#modalTableBody").html(table);

                    // Show modal
                    myModal.show();
                },
                error: function(){
                    alert("Error loading data.");
                }
                });
            });
$(document).off('click', '.ProjectsButton').on('click', '.ProjectsButton', function () {
    const id = $(this).data('id');
    const myModal = new bootstrap.Modal(document.getElementById('projectsModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
    $.get(`employees/projects/${id}`)
        .done(function (data) {
             $('#projectsModalLabel').text('Projects Assigned.');
            let html = "";
             if (data.length === 0) {
                    // No records case
                    html = "<li>No Projects Assigned</li>";
                } else {
                    data.forEach(member => {
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

$(document).off('click', '.TasksButton').on('click', '.TasksButton', function () {
    const id = $(this).data('id');
    const myModal = new bootstrap.Modal(document.getElementById('projectsModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
    $.get(`employees/tasks/${id}`)
        .done(function (data) {
             $('#projectsModalLabel').text('Tasks Assigned.');
            let html = "";
             if (data.length === 0) {
                    // No records case
                    html = "<li>No Tasks Assigned</li>";
                } else {
                    data.forEach(member => {
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

$(document).on('click', '.change_state-btn', function () {

const id = $(this).data('id');
const stat = $(this).data('status'); // Get the Type of the item
const url = '/employees/' + id + '/status_change';

// Confirm dialog
  $.ajax({
      url: url,
      type: 'POST',
      data: {
          _token: '{{ csrf_token() }}',
          status: stat 
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
    });
$('#userTable').DataTable({
    processing: true,
    serverSide: true,
     ajax: "{{ url()->full() }}",  // or your filtered route
    columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, // ✅ fix here
        { data: 'name', name: 'name' },
        { data: 'roles', name: 'roles' },
         @if($edit_permit || $delete_permit)
            { data: 'action', name: 'action', orderable: false, searchable: false }
        @endif
    ],
    pageLength: 10,
});
        /**confirmation alert box before deleting */
            $(document).on('click', '.delete-btn', function() {
                var id = $(this).data('id'); // Get the ID of the item
                var url='/employees/'+id;
                
                const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                backdrop: 'static',
                keyboard: false
            });
                myModal.show();
            // Add click listener for the Delete button inside the modal
            $('#confirmDelete').off('click').on('click', function () {
                $.ajax({
                        url: url, // Define your route with the ID
                        type: 'DELETE', // Use DELETE HTTP method
                        data: {
                            _token: '{{ csrf_token() }}' // CSRF token for security
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
                }); 
            });
});
        </script>
          @endsection