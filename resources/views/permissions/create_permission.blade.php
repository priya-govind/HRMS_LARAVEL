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
                    Add New Permission
                </div>
                <div class="float-end">
                    <a href="{{ route('permissions') }}" class="btn btn-primary btn-sm">&larr; Back</a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{route('store_permission')}}" method="post">
                    @csrf

                    <div class="mb-3 row">
                        <label for="permission_name" class="col-md-4 col-form-label text-md-end text-start">Permission Name</label>
                        <div class="col-md-6">
                          <input type="text" class="form-control @error('category_name') is-invalid @enderror" id="permission_name" name="permission_name" value="{{ old('permission_name') }}">
                            @error('permission_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <input type="submit" class="col-md-3 offset-md-5 btn btn-primary" value="Add Permission">
                    </div>
                    
                </form>
            </div>
        </div>
          </div>
          @endsection
         