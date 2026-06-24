@extends('/layouts.app')
@section('content')
      
      <!-- partial:partials/_navbar.html -->
      @include('/layouts.includes.topbar')
     
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
   
        <!-- partial:partials/_sidebar.html -->
        @include('/layouts.includes.sidebar')
        <!-- partial -->
        <div class="main-panel">
          
          <div class="content-wrapper">
            
          <div class="col-md-12 grid-margin stretch-card">
            
                <div class="card">
                  
                  <div class="card-body">
                  <nav class="p-1">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employees') }}">Employeees</a></li>
                <li class="breadcrumb-item active">Create Employee</li>
            </ol>
        </nav>
                  <div class="float-end">
                    <a href="{{ route('employees') }}" class="btn btn-primary btn-sm">&larr; Back</a><br/>
                </div>
         
                    <div class="card-title"><h4>Employee Details</h4></div>
               
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
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('success') }}
                        </div>
                    @endif
                    <form class="myForm" id="myForm"  action="{{ route('employees.update', $user['id']) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                     <hr/>
                    <h5 class="title">Basic Information</h5>
                   
                      <div class="row mx-3 my-3">
                        <div class="col-md-3 mb-3">
                            <label for="exampleInputName1">Name*</label>
                            <input type="text" class="form-control" id="name" name="name"  value="{{ $user['name'] }}" required  placeholder="Name">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="exampleInputEmail3">Email address*</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ $user['email'] }}" required autocomplete="username">
                        </div>
                      </div> 
                      <div class="row mx-3 my-3">
                      <div class="col-md-5 mb-3">
                            <label for="exampleTextarea1">Address*</label>
                            <textarea class="form-control" id="address" name="address" rows="4" required> {{ $user['address'] }} </textarea>
                        </div>
                      </div>
                        <div class="row  mx-3 my-3">
                        <div class="col-md-3 mb-3">
                             <label for="emp_status">Employee Status* &nbsp;&nbsp;</label>
                            <input type="radio" name="emp_status" id="emp_status_active"  @if($user['emp_status']=='1') checked @endif value="1"> Active &nbsp;&nbsp;&nbsp;
                            <input type="radio" name="emp_status" id="emp_status_inactive"  @if($user['emp_status']=='0') checked @endif  value="0"> Inactive
                            <div id="emp_status-error" class="error" style="display:none;"></div>
                        </div>
                        <div class="col-md-3 mb-3"> 
                            <label>Profile Picture*</label>
                            <input type="file" name="image" @if($user['image'] == '') required @endif >
                        <img src="{{ url('images/' . $user['image']) }}" alt="image" style="width: 25%; height: 40%;">
                        </div>
                    </div>
                      <div class="row mx-3 my-3">
                        
                        <div class="col-md-3 mb-3">
                            <label for="exampleTextarea1">Select Roles*</label>
                                <div class="checkbox-container">
                                    @foreach ($roles as $role)
                                    <label class="checkbox-item">
                                        <input type="checkbox" class="roleCheckbox" name="role_id[]" value="{{ $role->id }}" {{ in_array($role->id, $userRoles) ? 'checked' : '' }}> {{ $role->role_name }}
                                    </label>
                                    @endforeach    
                                </div>
                            <div class="invalid-feedback error" style="display:none;">
                                    Please select at least one role.
                            </div>
                        </div> 
                      </div>
                      <hr/>
                      <div class="row mx-3 my-3"  align="center">
                         <div class="mb-3">
                      <button type="submit" class="btn btn-gradient-primary me-2">Submit</button>
                      </div>
                      
                    </form>
                  </div>
                </div>
              </div>
          </div>
        </div>
        <script src="{{ asset('assets/js/edit_employee.js') }}"></script>
          @endsection