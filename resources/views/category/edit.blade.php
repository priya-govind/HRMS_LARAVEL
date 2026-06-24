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
                    Edit Category
                </div>
                <div class="float-end">
                    <a href="{{ route('category') }}" class="btn btn-primary btn-sm">&larr; Back</a>
                </div>
            </div>
            <div class="card-body">
            @if (session('success'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('success') }}
                        </div>
                    @endif
                    <form action="{{ route('update', ['category' => $category->id]) }}" method="POST">
                    @csrf

                    <div class="mb-3 row">
                        <label for="category_name" class="col-md-4 col-form-label text-md-end text-start">Category Name</label>
                        <div class="col-md-6">
                          <input type="text" class="form-control @error('category_name') is-invalid @enderror" id="category_name" name="category_name" value="{{$category->category_name}}">
                            @error('category_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
             
                    <div class="mb-3 row">
                        <label for="url_link" class="col-md-4 col-form-label text-md-end text-start">Category URL</label>
                        <div class="col-md-6">
                        <input type="text" class="form-control @error('url_link') is-invalid @enderror" id="url_link" name="url_link" value="{{ $category->url_link}}">
                            @error('url_link')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="url_link" class="col-md-4 col-form-label text-md-end text-start">Select Parent Category</label>
                        <div class="col-md-6">
                        <select name="parent_id" class="form-control form-control-sm">
                            <option value='1'>Select </option>
                            @foreach ($categories as $cat )
                            <option value="{{ $cat->id }}" @if($cat->id==$category->parent_id) selected @endif >{{ $cat->category_name }}</option>    
                            @endforeach
                            
                        </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="url_link" class="col-md-4 col-form-label text-md-end text-start">Category Status</label>
                        <div class="col-md-6">
                        <input type="radio" class="form-check-input"  name="is_active_cat" value="1"  @if( $category->is_active_cat==1) checked @endif  required>
                        <label class="form-check-label" for="is_active">Active</label>
                   
                        <input type="radio" class="form-check-input" name="is_active_cat" value="0" @if($category->is_active_cat==0) checked @endif required>
                        <label class="form-check-label" for="is_inactive">Inactive</label>

                    </div>
                            @error('is_active_cat')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    <div class="mb-3 row">
                        <input type="submit" class="col-md-3 offset-md-5 btn btn-primary" value="Edit Category">
                    </div>
                    
                </form>
            </div>
        </div>
          </div>
          @endsection
         