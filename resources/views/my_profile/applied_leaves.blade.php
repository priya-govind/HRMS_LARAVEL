@extends('layouts.app')
@section('content')
      
      <!-- partial:partials/_navbar.html -->
      @include('layouts.includes.topbar')
     
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('layouts.includes.sidebar')
        <style>
          /* General form styling */
.LeaveForm {
  border: 1px solid #ccc;
  border-radius: 10px;
  padding: 20px;
  background-color: #fdfdfd;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}


/* Prevent full-width stretching */
.form-control {
  width: auto;
  min-width: 150px;
  max-width: 100%;
  display: inline-block;
}

/* Radio buttons inline */
.leave_type {
  margin-right: 5px;
}

.mb-3 {
  margin-bottom: 1rem;
}

/* Align labels and inputs neatly */
.form-label {
  font-weight: bold;
  margin-bottom: 4px;
  display: inline-block;
}

/* Textarea sizing */
textarea#reason {
  width: 100%;
  min-height: 80px;
  resize: vertical;
  padding: 6px;
  border-radius: 4px;
  border: 1px solid #ccc;
}

/* Button styling */
#saveBtn {
  margin-top: 10px;
  padding: 6px 16px;
  font-size: 14px;
}
select.form-control{
    padding: 0.4375rem 0.75rem;
    border: 1px solid #aaa;
    outline: 1px solid #ced4da;
    color: #6c757d;
    width: 75%;
    border-radius: 3px;
    background-color: transparent;
    appearance: auto; /* For most modern browsers */
    -webkit-appearance: auto; /* Safari/Chrome */
    -moz-appearance: auto; /* Firefox */

}
</style>
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">

          <div class="row">
              <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-header">
                    <div class="float-start">
                        Applied Leave
                    </div>
                  </div> 
              <div class="card-body">

               <div class="row">
                <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                <div class="col-md-12 apply_leave">
                <form id="LeaveForm" class="LeaveForm" method="post">
                    @csrf
                    <h5><b> Apply For Leave/Permission </b></h5><br/>
                     <div class="mb-3">
                        <label for="name" class="form-label">LeaveType:</label>&nbsp;&nbsp;
                        <input type="radio" class="form-check-input leave_type" name="leave_type" value="1" required> Leave &nbsp;&nbsp;
                        <input type="radio" class="form-check-input leave_type" name="leave_type" value="2" required>Permission
                      </div>
                      <div class="row leave_cl">
                          <div class="col-md-2 mb-3">
                              <label for="name" class="form-label">From Date:</label>
                              <input type="text" class="form-control" id="from_dt" name="from_dt">
                          </div>
                          <div class="col-md-2 mb-3">
                              <label for="name" class="form-label">To Date:</label>
                              <input type="text" class="form-control" id="to_dt" name="to_dt">
                          </div>
                      </div>
                      <div class="permit_cl">
                          <div class="col-md-2 mb-3">
                            <label for="name" class="form-label">Date:</label>
                            <input type="text" class="form-control" name="permission_dt" id="permission_dt">
                          </div>
                          <div class="row">
                              <div class="col-md-2 mb-3">
                                  <label for="name" class="form-label">From Time:</label>
                                  <input type="text" class="form-control" id="from_time" name="from_time">
                              </div>
                              <div class="col-md-2 mb-3">
                                  <label for="name" class="form-label">To Time:</label>
                                  <input type="text" class="form-control" id="to_time" name="to_time">
                              </div>
                          </div>
                      </div>
                    <div class="col-md-4 mb-3">
                        <label for="name" class="form-label">Reason:</label>
                        <textarea name="reason" id="reason"> </textarea>
                    </div>
                  <button type="submit" class="btn btn-primary" id="saveBtn">Apply</button>
                  <img class="sender_load" src="{{url('assets/images/small_load.gif')}}" style="margin: 0 25% 0 0;display:none;"/>
                </form>
                </div>
            </div>
            <div class="row">
              <div class="col-md-12 search">
                <form id="FilterForm" class="LeaveForm" method="post">
                    @csrf
                  <h5><b> Search History for Applied Leave Details</b></h5><br/>
                     <div class=" row mb-3">
                        <label for="name" class="form-label">Filter By:</label>
                          <div class="col-md-2 mb-3">
                              <input type="text" class="form-control" id="from_dt_search" name="from_dt_search" placeholder="From Date" >
                          </div>
                          <div class="col-md-2 mb-3">
                              <input type="text" class="form-control" id="to_dt_search" name="to_dt_search" placeholder="To Date">
                          </div>
                                 <div class="col-md-2 mb-3">
                        <select name="leave_type" id="leave_type" class="form-control frm_ctrl_select">
                            <option value=""> Select Leave Type</option>
                            <option value="1">Leave</option>
                            <option value="2">Permission</option>
                          </select>
                      </div>
                         <div class="col-md-2 mb-3">

                  <button type="submit" class="btn btn-primary" id="saveBtn">Search</button>
                  {{-- <img class="sender_load" src="{{url('assets/images/small_load.gif')}}" style="margin: 0 25% 0 0;display:none;"/> --}}
                  <button type="button" class="btn btn-secondary" id="clearFiltersBtn">Clear Search</button>
                         </div>
                      </div>
                 
                </form>
                </div>
            </div>
                <table  id="LeaveTable" class="display table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sno</th>
                                        <th>Applied Date</th>
                                        <th>Leave Type</th>
                                        <th>Details</th>
                                        <th>Leave Status</th>
                                        <th>Action</th>
                                    </tr>

                                </thead>
                              <tbody>
                              
                            </tbody>
                          </table>
                  </div>
                </div>
              </div>
            </div>
                       <div class="modal fade" id="LeaveModal" tabindex="-1" aria-labelledby="LeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content logic_cont">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="LeaveModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                <form id="dataForm">
                    @csrf
                    <input type="hidden" id="recordId" />
                    <input type="hidden" id="emp_id" name="emp_id" />
                     <div class="mb-3">
                        <label for="name" class="form-label">LeaveType:</label>&nbsp;&nbsp;
                        <input type="radio" class="form-check-input" name="leave_type1" value="1" disabled> Leave &nbsp;&nbsp;
                        <input type="radio" class="form-check-input" name="leave_type1" value="2" disabled>Permission
                      </div>
                      <div class="row leave_cl">
                          <div class="col-md-5 mb-3">
                              <label for="name" class="form-label">From Date:</label>
                              <input type="text" class="form-control" id="from_dt1" name="from_dt" readonly>
                          </div>
                          <div class="col-md-5 mb-3">
                              <label for="name" class="form-label">To Date:</label>
                              <input type="text" class="form-control" id="to_dt1" name="to_dt" readonly>
                          </div>
                      </div>
                      <div class="permit_cl">
                          <div class="col-md-5 mb-3">
                            <label for="name" class="form-label">Date:</label>
                            <input type="text" class="form-control" name="permission_dt" id="permission_dt1" readonly>
                          </div>
                          <div class="row">
                              <div class="col-md-5 mb-3">
                                  <label for="name" class="form-label">From Time:</label>
                                  <input type="text" class="form-control" id="from_time1" name="from_time" readonly>
                              </div>
                              <div class="col-md-5 mb-3">
                                  <label for="name" class="form-label">To Time:</label>
                                  <input type="text" class="form-control" id="to_time1" name="to_time" readonly>
                              </div>
                          </div>
                      </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Reason:</label>
                        <textarea name="reason" id="reason1" readonly> </textarea>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Leave Status:</label>
                         <span id="leave_status" name="leave_status"></span>
                    </div>
                      <div class="mb-3" id="leave_cmt" style="display:none">
                        <label for="name" class="form-label">Comments Added will Updating Status:</label>
                         <span id="view_comments" name="view_comments"></span>
                    </div>
                  <button type="button" class="btn btn-danger" style="margin: 9px 0 0 0;" data-bs-dismiss="modal">Close</button>
                </form>
            </div>
        </div>
    </div>
</div> 
          </div>
<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function () {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(function (tooltipTriggerEl) {
    new bootstrap.Tooltip(tooltipTriggerEl, {
      html: true
    });
  });
});
</script>
<script src="{{ asset('assets/js/common.js') }}"></script>
<script src="{{ asset('assets/js/leaves_mgmt.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#LeaveTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
       ajax: {
              url: "{{ route('attendance.applied_leaves') }}",
              type: "GET",
              data: function (d) {
                  d.leave_type = $('#leave_type').val();
                  d.from_dt = $('#from_dt_search').val();
                  d.to_dt = $('#to_dt_search').val();
              }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false  },
            { data: 'applied_date', name: 'applied_date' },
            { data: 'leave_type', name: 'leave_type' },
            { data: 'details', name: 'details' },
            { data: 'leave_status', name: 'leave_status' , orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        error: function (xhr, error, thrown) {
            alert('Something went wrong while loading the data.');
        }
    });
});
</script>

          @endsection
         