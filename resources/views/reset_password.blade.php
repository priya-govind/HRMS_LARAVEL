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
                    <div class="row">
                      <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                                @csrf
                            <div class="col-md-12 panel-right">
                              <h1>Reset Password</h1>
                              <h6 class="text-white">Enter your email to reset password</h6>
                              <div class="form-group">
                                <label class="text-white">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                              </div>
                              <div class="form-group">
                                <label class="text-white">New password</label>
                                <input type="password" class="form-control" name="password" placeholder="New password" required>
                              </div>
                              <div class="form-group">
                                <label class="text-white">Confirm password</label>
                                <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm password" required>
                              </div>
                              <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-focus">Change Password</button>
                              </div>
                            </div>
                            </form>
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