@extends('layouts.app')
@section('content')
      <meta name="csrf-token" content="{{ csrf_token() }}">

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
                    List All Notifications
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
                            <th>Notification Subject</th>
                            <th>Message</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
</table>
                    </div>
                  
                               
                  
                </div>
          </div>

<div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content logic_cont">
            <div class="modal-header">
                <h5 class="modal-title" id="dataModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                <form id="dataForm">
                    @csrf
                    <input type="hidden" id="recordId" name="id">
                    <div class="mb-3">
                        <label for="name" class="form-label">Notification Type Name</label>
                        <input type="text" class="form-control" id="notify_type" name="notify_type" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed">
                    </div>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                </form>
            </div>
        </div>
    </div>
</div> 
@include('layouts.includes.delete_popup')              
<script type="text/javascript">
        $(document).ready(function () {
        $('#categoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('notifications.alert_notifications') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'subject', name: 'subject' },
                     { data: 'message', name: 'message' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
            });
        });
    </script>
<!-- <script src="{{ asset('assets/js/common.js') }}"></script> -->
<script src="{{ asset('assets/js/notify_type.js') }}"></script>
@endsection