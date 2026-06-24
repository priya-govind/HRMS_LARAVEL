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
                   Generated Bills
                </div>
                <div class="float-end">
                    <button type="button" id="newbillButton" class="btn btn-primary btn-sm">+Add New Bill
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
                         <table id="ExpenseTypeTable" class="display table table-bordered">
                                <thead>
                                <tr>
                                    <th>S.no</th>
                                    <th>Bill Type </th>
                                    <th>Bill Name</th>
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
                <form id="databillForm" method="POST" enctype="multipart/form-data"> 
                    @csrf
                    <input type="hidden" id="recordId" name="id">
                    <div class="mb-3">
                        <label for="bill_type" class="form-label">Bill Type</label>
                        <select name="bill_type" id="bill_type" class="form-control">
                            <option value="">Select</option>
                            @foreach($bill_type as $id =>$val)
                            <option value="{{ $id }}">{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bill_name" class="form-label">Bill Name</label>
                        <input type="text" name="bill_name" id="bill_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="bill_date" class="form-label">Purchased Date</label>
                        <input type="text" name="bill_date" id="bill_date" class="form-control">
                    </div>
                     <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="text" name="amount" id="amount" class="form-control">
                    </div>
                     <div class="mb-3">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea name="comments" id="comments" class="form-control"> </textarea>
                    </div>
                    <div class="mb-3">
                        <label for="bill_path" class="form-label">Upload Photocopy</label>
                        <input type="file" name="bill_path" id="bill_path" class="form-control">
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
        $('#ExpenseTypeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('bills.generate_bills') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'bill_type', name: 'bill_type' },
                    { data: 'bill_name', name: 'bill_name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 25,
            });
        });
                $("#bill_date").datepicker({
        dateFormat: "dd-mm-yy",
        onSelect: function(selectedDate) {
            var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate);
            var minDate = new Date(dateObj);
            minDate.setDate(minDate.getDate() + 1);
            $("#end_date").datepicker("option", "minDate", minDate);
        }
    });
    </script>
<!-- <script src="{{ asset('assets/js/common.js') }}"></script> -->
<script src="{{ asset('assets/js/work_mode.js') }}"></script>
@endsection