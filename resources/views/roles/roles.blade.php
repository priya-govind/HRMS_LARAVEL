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
                    List Roles
                </div>
                <div class="float-end">
                    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">+Add New Roles</a>
                </div>
              
            </div>
                    <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('success') }}
                        </div>
                    @endif
                    <table  id="rolesTable" class="display table table-bordered">
                                <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Roles</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                  
                                </tbody>
                        </table>
                    </div>
                </div>
          </div>
          <script type="text/javascript">
            // Ensure jQuery is ready
            $(document).ready(function () {
                $('#rolesTable').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: "{{ route('roles') }}",
                            columns: [
                                 { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                                { data: 'role_name', name: 'role_name' },
                                { data: 'action', name: 'action', orderable: false, searchable: false }
                            ],
                             pageLength: 10,
                        });
                    });
        </script>
          @endsection
         