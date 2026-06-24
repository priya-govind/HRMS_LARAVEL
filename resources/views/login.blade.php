@extends('layouts.app')
@section('content')
<style>
     #email-error,#password-error,#role_id-error {
        border-color: white !important;
        color: white !important;
     }
      #role_id.is-invalid {
    color: #1573b0 !important;
    background-color: white !important;
    border-color: white !important;
    }

/* Ensure dropdown options are navy too */
#role_id.is-invalid option {
  color: #1573b0 !important;
}

  </style>
<div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth">
          <div class="row flex-grow">
            <div class="col-lg-4 mx-auto">
              <div class="auth-form-light text-left p-5">
                <div class="brand-logo">
                  <img src="{{url('assets/images/fort_logo.png')}}">
                  <h1>LOGIN </h1>
                </div>
                @if (session('error'))
                        <div class="alert alert-warning" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('status') }}.Log in With New Details
                        </div>
                    @endif
                <form id="LoginForm" class="pt-3 login_form" action="{{ route('login') }}" method="post">
                  @csrf

                  <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control form-control-lg" placeholder="Email">
                  </div>

                  <div class="form-group">
                    <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Password">
                  </div>

                  <!-- Editable dropdown for multiple roles -->
                  <div class="form-group" id="role_group" style="display: none;">
                    <select class="form-control form-control-lg" name="role_id" id="role_id">
                      <option value="">Select Role</option>
                    </select>
                  </div>

                  <!-- Display-only dropdown for single role -->
                  <div class="form-group" id="role_display_group" style="display: none;">
                    <select class="form-control form-control-lg" id="role_id_display" disabled></select>
                  </div>

                  <div class="mt-3 d-grid gap-2">
                    <button class="btn btn-block btn-gradient-white btn-lg font-weight-medium auth-form-btn" type="submit" style="background: white; color: #215dab;">SIGN IN</button>
                  </div>

                  <div class="my-2 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                      <label class="form-check-label text-white">
                        <input type="checkbox" class="form-check-input" name="remember" id="remember"> Keep me signed in
                      </label>
                    </div>
                    <a href="{{ route('password.email') }}" class="auth-link text-primary">Forgot password?</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
<script>
$(document).ready(function () {
    function checkRoles(callback) {
        const email = $('#email').val().trim();
        if (!email) {
            if (callback) callback();
            return;
        }

        $.ajax({
            url: '{{ route("check.roles") }}',
            type: 'POST',
            data: {
                email: email,
               // _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                const roleSelect = $('#role_id');
                const roleDisplay = $('#role_id_display');
                const roleGroup = $('#role_group');
                const roleDisplayGroup = $('#role_display_group');

                roleSelect.empty();
                roleDisplay.empty();
                $('input[name="role_id"]').remove();

                if (response.status === 'found') {
                    const roles = response.roles;
                    const count = response.count;

                    if (count > 1) {
                        roleSelect.append('<option value="">Select Role</option>');
                        $.each(roles, function (i, role) {
                            roleSelect.append(`<option value="${role.id}">${role.role_name}</option>`);
                        });

                        roleGroup.show();
                        roleDisplayGroup.hide();
                    } else if (count === 1) {
                        const singleRole = roles[0];

                        roleDisplay.append(`<option>${singleRole.role_name}</option>`);
                        roleDisplayGroup.hide();
                        roleGroup.hide();

                        $('<input>').attr({
                            type: 'hidden',
                            name: 'role_id',
                            value: singleRole.id
                        }).appendTo('#LoginForm');
                    }
                } else {
                    roleGroup.hide();
                    roleDisplayGroup.hide();
                }

                if (callback) callback();
            }
        });
    }

    // Initialize validation FIRST
    $('#LoginForm').validate({
        rules: {
            email: { required: true },
            password: { required: true },
            role_id: {
                required: function () {
                    return $('#role_id').is(':visible');
                }
            }
        },
        messages: {
            email: 'Email Field is required.',
            password: 'Password is required.',
            role_id: 'Select the role',
        },
        errorClass: "is-invalid",
        errorElement: "label",
        errorPlacement: function (error, element) {
            element.after(error);
        },
        ignore: [] // important: validate hidden fields too
    });

    // Run on blur
    $('#email').on('blur', function () {
        checkRoles();
    });

    $('#LoginForm').on('submit', function (e) {
        e.preventDefault();

        // Get role_id value (adjust selector if it's hidden input or dropdown)
        let roleId = $('#role_id').val();

        if (!roleId) {
            // Only call checkRoles when role_id is empty
            checkRoles(function () {
                if ($('#LoginForm').valid()) {
                    $('#LoginForm')[0].submit(); // safe submit after roles loaded
                }
            });
        } else {
            //  If role_id already exists, skip checkRoles and submit directly
            if ($('#LoginForm').valid()) {
                $('#LoginForm')[0].submit();
            }
        }
    });

});
</script>
    @endsection