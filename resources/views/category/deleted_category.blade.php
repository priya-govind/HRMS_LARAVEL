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
                    List Deleted Categories
                </div>
                
              
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
                    <table id="categoryTable" class="display table table-bordered">
                                <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Categories</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                
                                </tbody>
                        </table><br/>
                        
                    </div>
                  
                               
                  
                </div>
          </div>
        

<script type="text/javascript">
// Ensure jQuery is ready
$(document).ready(function () {


    $('#categoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('deleted_category') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                 pageLength: 10,
            });
// Confirmation Alert Box before Deleting
$(document).on('click', '.restore-btn', function () {
const id = $(this).data('id');
const url = '/category/' + id + '/restore';

// Confirm dialog
if (confirm("Are you sure want to restore deleted item?")) {
  $.ajax({
      url: url,
      type: 'POST',
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
}
});
});
</script>
          @endsection
         