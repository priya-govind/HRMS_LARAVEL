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
            <table id="assignmentTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Inventory Item</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
          </div>
          @endsection
         @push('scripts')
            <script>
            $(function() {
                $('#assignmentTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('inventory_assignment.index') }}",
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                        { data: 'asset_name', name: 'asset_name' },
                        { data: 'assigned_to', name: 'assigned_to' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ]
                });
            });
            </script>
        @endpush

