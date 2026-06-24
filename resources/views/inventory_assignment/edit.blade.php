@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Assignment</h2>
    <form action="{{ route('inventory_assignment.update', $inventory_assignment->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('inventory_assignment.partials.form')
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection