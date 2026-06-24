@extends('layouts.app')

@section('content')
<h2>Add Inventory Item</h2>
<form method="POST" action="{{ route('inventory.store') }}">
    @csrf
    <input type="text" name="name" placeholder="Item Name" required>
    <input type="text" name="type" placeholder="Type" required>
    <input type="text" name="serial_number" placeholder="Serial Number" required>
    <button type="submit" class="btn btn-success">Save</button>
</form>
@endsection
