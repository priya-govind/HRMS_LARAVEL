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
          <div class="card-header">
                <div class="float-start">
                    List Roles
                </div>
                <div class="float-end">
                <button type="button" id="addButton" class="btn btn-primary btn-sm">+Add New Roles</button>
                </div><br/><br/><br/>
              
            </div>
            <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('success') }}
                        </div>
                    @endif
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Roles</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if($roles->isNotEmpty())
                                    @foreach ($roles as $role)         
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $role->role_name }}</td>
                                        <td>
                                        <button type="button" data-id="{{$role->id}}" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>
                                        <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="{{$role->id}}"  data-type="user"><i class="fa fa-trash-o"></i></button>
                                        </td>
                                    </tr>   
                                @endforeach
                                @else 
                            <tr>
                                <td colspan="3" align="center">No Records Found </td>
                            </tr>
                                @endif
                                </tbody>
                        </table>
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
                            <form id="dataForm">
                                @csrf
                                <input type="hidden" id="recordId" name="id">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="" name="role_name" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed" required>
                                </div>

                                <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
          </div>
          <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script type="text/javascript">
  /*For no conflict* */
      //  var jq = $.noConflict();
      $(document).ready(function () {
    // Open Modal for Adding a New Record
    $('#addButton').click(function () {
        var myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static', // Prevent closing when clicking outside
        keyboard: false // Optional: Prevent closing with the Escape key
        });
        myModal.show();
        $('#dataModalLabel').text('Add New Role');
        $('#dataForm').trigger('reset');
        $('#recordId').val('');
        $('#dataModal').modal('show');
    });

    // Open Modal for Editing a Record
    $('.editButton').click(function () {
        var myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static', // Prevent closing when clicking outside
        keyboard: false // Optional: Prevent closing with the Escape key
        });
        myModal.show();
        var id = $(this).data('id');
       
        $.get('/roles/' + id + '/edit', function (data) { // Adjusted to match RESTful route
            $('#dataModalLabel').text('Edit Role');
            $('#recordId').val(data.id);
            $('#role_name').val(data.role_name);
            $('#dataModal').modal('show');
        });
    });

    // Handle Form Submission for Add/Edit
    $('#dataForm').submit(function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        // Determine URL and HTTP method for Add/Edit
        var url = $('#recordId').val()
            ? '/roles/' + $('#recordId').val() // RESTful URL for update
            : '/roles'; // RESTful URL for store
        var method = $('#recordId').val() ? 'PUT' : 'POST'; // Use PUT for update and POST for store
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function (response) {
                alert(response.success); 
                $('#dataModal').modal('hide');
                location.reload(); // Refresh the page
            },
            error: function (xhr) {
                alert('Error: ' + xhr.responseJSON.message);
            }
        });
    });
    
        /**confirmation alert box before deleting */
$(document).on('click', '.delete-btn', function() {
    var id = $(this).data('id'); // Get the ID of the item
    var url='/roles/'+id;
    

    if (confirm("Are you sure you want to delete this item?")) {
      $.ajax({
            url: url, // Define your route with the ID
            type: 'DELETE', // Use DELETE HTTP method
            data: {
                _token: '{{ csrf_token() }}' // CSRF token for security
            },
            success: function(response) {
                alert(response.message); 
                location.reload(); // Show success message
               
            },
            error: function(xhr, status, error) {
                alert('Error: ' + xhr.responseJSON.message); // Show error message
            }
        });
    } 
});
});

        </script>
          @endsection    