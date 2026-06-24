@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Inventory Assignments</h2>
    <a href="{{ route('inventory_assignment.create') }}" class="btn btn-success mb-3">New Assignment</a>

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