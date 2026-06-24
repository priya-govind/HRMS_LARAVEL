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
                    List Deleted Employees
                </div>
                <div class="float-end">
                <a href="{{route('employees.create')}}"  class="btn btn-primary btn-sm">+Add New Employees</a>
                </div><br/><br/><br/>
              
            </div>
            <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" type="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('success') }}
                        </div>
                    @endif
                    <table id="userTable" class="display table table-bordered">
                                <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Users</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                              
                                </tbody>
                        </table>
                    </div> 
          </div>
          
          <script type="text/javascript">
// Ensure jQuery is ready
$(document).ready(function () {
    $('#userTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('deleted_employees') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'name', name: 'name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });


// Confirmation Alert Box before Deleting
$(document).on('click', '.restore-btn', function () {
const id = $(this).data('id');
const url = '/employees/' + id + '/restore';

// Confirm dialog
if (confirm("Are you sure want to restore deleted item?")) {
  $.ajax({
      url: url,
      type: 'POST',
      data: {
          _token: '{{ csrf_token() }}'
      },
      success: function (response) {
          alert(response.message); // Single alert on success
          location.reload(); // Refresh page
      },
      error: function (xhr) {
          alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred')); // Single alert on error
      }
  });
}
});
});
</script>
          @endsection    