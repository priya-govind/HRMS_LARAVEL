@extends('layouts.app')

@section('content')
<h3>Assign Inventory</h3>

<form method="POST" action="{{ route('assignments.store') }}">
    @csrf
    <div class="mb-3">
        <label>Employee</label>
        <select name="employee_id" class="form-select" required>
            <option value="">Select</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Inventory</label>
        <select name="inventory_id" class="form-select" required>
            <option value="">Select</option>
            @foreach($inventory as $item)
                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->serial_number }})</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Assign</button>
</form>
@endsection