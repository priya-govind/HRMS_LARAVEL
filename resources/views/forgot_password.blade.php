@extends('layouts.app')
@section('content')
<div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth">
          <div class="row flex-grow">
            <div class="col-lg-4 mx-auto">
                 @if (session('error'))
                        <div class="alert alert-warning" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('status') }}
                        </div>
                    @endif
              <div class="auth-form-light text-left p-5">
                <div class="brand-logo">
                  <img src="{{url('assets/images/fort_logo.png')}}">
                </div>
                    <div class="container-fluid">
      <div class="row">
     <form action="{{route('password.email')}}" method="POST">
         @csrf
        <div class="col-md-12 panel-right">
          <h1>Reset Password</h1>
          <h6 class="text-white">Enter your email to reset password</h6>
          <div class="form-group">
            <label class="text-white">Email Address</label>
            <input type="email" class="form-control"  name="email" placeholder="Enter your email" required>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-primary btn-focus">Submit</button>
          </div>
        </div>
</form>

      </div>
    </div>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    @endsection