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
    <div class="card-header">
        <h5 class="mb-0">Birthday Remainder Management</h5>
    </div>
<hr/>
    <div class="card-body">
        {{-- Success Message --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
          @if (session('error'))
                        <div class="alert alert-warning  alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('error') }}
                        </div>
                @endif
        
        <div class="mb-4 border-bottom pb-3">
            <h6 class="mb-3"><i class="fa fa-upload" aria-hidden="true"></i> Import All Employee Birthdays</h6>
            <form method="POST" action="{{ route('birthday_remainder') }}" id="birthdayForm" enctype="multipart/form-data">
                @csrf
                <div class="card" style="padding:40px;">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="file" class="form-label">Select File to Upload</label>
                            <input type="file" name="birthday_file" class="form-control" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">Import</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="float-start">
                    Birthday List
                </div>
            </div>
                    <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('success') }}
                        </div>
                    @endif
                         <br/>
                         <div id="success-message" class="alert alert-success"  role="alert"  style="display: none;"></div>
                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                         <table id="categoryTable" class="display table table-bordered">
                            <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Employee Name</th>
                                <th>Birth Date</th>
                            </tr>
                            </thead>
                            <tbody> </tbody>
                        </table>
                    </div>
                </div>
    </div>
  
          </div>
          <script>

               $("#birthdayForm").validate({
                    errorClass: "is-invalid",
                    rules: {
                        file: {
                            required: true,
                        },
                    },
                    messages: {
                        file: {
                            required: "Upload File is Required.",
                        },
                    },
                    highlight: function (element, errorClass) {
                        $(element).addClass(errorClass);
                    },
                    unhighlight: function (element, errorClass) {
                        $(element).removeClass(errorClass);
                    },
                    errorPlacement: function (error, element) {
                        error.appendTo(element.parent());
                    },
                    submitHandler: function (form) {
                        form.submit();
                    },
                });

        //   $('.download_punch_attendance').on('click',function(){
        //        const downloadUrl = '{{ route('attendance.export') }}';
        //         window.location.href = downloadUrl;
        //     });
        $(document).ready(function () {
               $('#categoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('birthday_remainder_list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'employee_name', name: 'employee_name' },
                    { data: 'birth_date', name: 'birth_date' },
                ],
                pageLength: 25,
            });
        });
         </script>
          @endsection
