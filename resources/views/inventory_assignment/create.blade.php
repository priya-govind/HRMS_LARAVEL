@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Assignment</h2>
    <form action="{{ route('inventory_assignment.store') }}" method="POST">
        @csrf
        @include('inventory_assignment.partials.form')
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection