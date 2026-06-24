@extends('layouts.admin_app')
@section('content')
<div class="container-fluid ">
        <div class="row border-top border-light">
            <div class="col-2 bg-dark text-white" style="height: 640px;">
            @include('layouts.includes.side_bar')
            </div>
            <div class="col mt-3">
                <div class="col-12">
                <h3>{{ $page_info['category_name'] }}</h3>
        <div class="d-flex gap-3 justify-content mt-5">
            
            <div class="content">
            {{ $page_info['content'] }}
        </div>
    
        </div>
                </div>
                <div class="col-12">
                    <br/><br/>
                <div class="d-flex justify-content-center gap-3">
                @can('CategoryPermit', [$page_info['id'], $permission['add']])
                <a href="#" class="btn btn-primary btn-sm"> Add</a><br/>
                @endcan
                @can('CategoryPermit', [$page_info['id'], $permission['edit']])
                <a href="#" class="btn btn-primary btn-sm"  data-bs-toggle="modal" data-bs-target="#updatepagepopup"> Edit</a><br/>
 
                @endcan
                @can('CategoryPermit', [$page_info['id'], $permission['delete']])
                <a href="#" class="btn btn-primary btn-sm"> Delete</a><br/>
                @endcan
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">&larr; Back</a>
                </div>
            </div>
            </div>
           
        </div>
        
    </div>
    <div class="modal fade" id="updatepagepopup" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="width:120%">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Page Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
                <form action="{{route('update',$page_info['id'])}}"  method="post">
                    @csrf
                    <div data-mdb-input-init class="form-outline mb-4">
                    <label class="form-label" for="name">Page Name</label>    
                    <input type="text" id="category_name" name="category_name" class="form-control" value="{{$page_info['category_name']}}" required />
                        
                    </div>

                    <div data-mdb-input-init class="form-outline mb-4">
                    <label class="form-label" for="content">Content</label>    
                    <textarea name="content" class="form-control" style="height: 121px;" required>{{ $page_info['content'] }} </textarea>  
                    </div>

                
            </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
      </form>
    </div>
  </div>
</div>
    @endsection

