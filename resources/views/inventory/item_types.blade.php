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
                    List of Item Types
                </div>
                <div class="float-end">
                    <button type="button" id="addButton" class="btn btn-primary btn-sm">+Add New Item Type
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
                         <table id="WorkModeTable" class="display table table-bordered">
                                <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Item Type Name</th>
                                    <th>Status</th>
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
                        <label for="name" class="form-label">Item Type Name</label>
                        <input type="text" class="form-control" id="item_type_name" name="item_type_name">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Item Type Status:</label>&nbsp;&nbsp;
                        <input type="radio" class="form-check-input" id="item_status" name="item_status" value="1" required> Active &nbsp;&nbsp;
                        <input type="radio" class="form-check-input" id="item_status" name="item_status" value="0" required>Inactive
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
        $('#WorkModeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('item_types.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'item_type_name', name: 'item_type_name' },
                    { data: 'item_status', name: 'item_status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
            });
        });
    </script>
<!-- <script src="{{ asset('assets/js/common.js') }}"></script> -->
<script src="{{ asset('assets/js/item_type.js') }}"></script>
@endsection