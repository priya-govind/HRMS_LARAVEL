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
                    Add New Category
                </div>
                <div class="float-end">
                    <a href="" class="btn btn-primary btn-sm">&larr; Back</a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{route('store')}}" method="post">
                    @csrf
                    @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                    </div>
                    @endif
                    <div class="mb-3 row">
                        <label for="category_name" class="col-md-4 col-form-label text-md-end text-start">Category Name</label>
                        <div class="col-md-6">
                          <input type="text" class="form-control @error('category_name') is-invalid @enderror" id="category_name" name="category_name" value="{{ old('category_name') }}" required>
                            @error('category_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <table  class="table table-bordered">
                        <thead>
                            <tr>
                            <th>Pages</th>
                            @foreach ($permissions as $permission)
                                <th>{{ $permission->permission_name }}</th>
                            @endforeach
                                </tr>
                        </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                <?php $i=0;?>
                                    <tr>
                                        <td><b>{{ $category->category_name }}</b></td>
                                        @foreach ($permissions as $permission)
                                        @if ($loop->first)
                                            <td>
                                                <input type="checkbox" 
                                                    name="permissions[{{ $category->id }}][{{ $permission->id }}]" 
                                                    value="1" class="row-check">
                                            </td>
                                        @else
                                            <td>
                                                <input type="checkbox" 
                                                    name="permissions[{{ $category->id }}][{{ $permission->id }}]" 
                                                    value="1" class="item-check" disabled>
                                            </td>
                                        @endif
                                        @endforeach

                                    </tr>
                                    @foreach ($category->children as $subCategory) 
                                    <tr>
                                        <td>{{ $subCategory->category_name }}</td>
                                        @foreach ($permissions as $permission)
                                        @if ($loop->first)
                                            <td>
                                                <input type="checkbox" 
                                                    name="permissions[{{ $subCategory->id }}][{{ $permission->id }}]" 
                                                    value="1" class="row-check">
                                            </td>
                                        @else
                                            <td>
                                                <input type="checkbox" 
                                                    name="permissions[{{ $subCategory->id }}][{{ $permission->id }}]" 
                                                    value="1" class="item-check" disabled>
                                            </td>
                                        @endif
                                        @endforeach
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                    </table>
                    </div>
                    
                    <div class="mb-3 row">
                        <input type="submit" class="col-md-3 offset-md-5 btn btn-primary" value="Add Category">
                    </div>
                    
                </form>
            </div>
        </div>
          </div>
          @endsection
         