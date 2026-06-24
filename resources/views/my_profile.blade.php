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
          <div class="col-md-8 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">My Profile</h4>
                    
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
                    <form class="myForm" action="{{route('myprofile_update',$user['id'])}}" method="post" enctype="multipart/form-data">
                    @method('PUT') 
                    @csrf
                      <div class="form-group">
                        <label for="exampleInputName1">Name</label>
                        <input type="text" class="form-control" id="name" name="name" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed" value="{{$user['name']}}" required  placeholder="Name">
                        <div class="invalid-feedback">Please fill out this field.</div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail3">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ $user['email'] }}" required>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputPassword4">Password</label>
                        <input type="password" class="form-control"  id="password" name="password" placeholder="Password" value="{{ $user['password'] }}" required>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputPassword4">Re Type Password</label>
                        <input type="password" class="form-control"  id="confirm_password" name="confirm_password" placeholder="Password" value="{{ $user['password'] }}" required>
                      </div>
                      <div class="form-group">
                        <label for="exampleSelectGender">Gender</label>
                          <input type="radio" name="gender" value="male" @if($user['gender']=='male') checked @endif required>Male
                          <input type="radio" name="gender" value="female" @if($user['gender']=='female') checked @endif  required>Female
                        
                      </div>
                      <div class="form-group">
                        <label>Image upload</label>
                        <label for="image">Image Upload:</label><br/>
                      <input type="file" id="image" name="image" class="file-upload" @if($user['image']=='') required @endif tabindex="0">
                    
                        <img src="{{url('images/'.$user['image'])}}" alt="image" style="width: 10%;height: 10%;">
                        
                      </div>
                      <div class="form-group">
                        <label for="exampleTextarea1">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="4"required> {{ $user['address'] }}</textarea>
                      </div>
                      <button type="submit" class="btn btn-gradient-primary me-2">Submit</button>
                      
                    </form>
                  </div>
                </div>
              </div>
         
           
          </div>
          {{-- <script>
            document.querySelector('form').addEventListener('submit', function(event) {
    const checkboxes = document.querySelectorAll('.roleCheckbox');
    const isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

    if (!isChecked) {
        event.preventDefault();
        alert('Please select at least one role.');
    }
});

            </script> --}}
          @endsection
         