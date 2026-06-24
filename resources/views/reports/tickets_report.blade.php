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
            <div class="row justify-content-center mt-4">
          <div class="col-md-12">
              <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: #215dab">
                  <h5 class="mb-0">Export Tickets Report</h5>
                </div>
                <div class="card-body">`
                  <form  id="AttendanceReport">
                    @csrf
                    <div class="row form-row align-items-start">
                      <div class="form-group col-md-2">
                        <label for="startDate">Start Date</label>
                        <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="{{ request('start_date') }}"  readonly>
                      </div>
                      <div class="form-group col-md-2">
                        <label for="endDate">End Date</label>
                        <input type="text" class="form-control" id="end_date" name="end_date" placeholder="End Date" value="{{ request('end_date') }}" readonly>
                      </div>
                      <div class="form-group col-md-2">
                          <label for="teamType"> Ticket Status</label>
                            <select class="form-control" id="ticket_status" name="ticket_status">
                              <option value="">Select</option>
                                @foreach ($project_status as $id =>$status)
                                  <option value="{{ $id  }}" {{ request('ticket_status') == $id ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                      </div>
                      <div class="form-group col-md-2">
                          <label for="teamType">Team Members</label>
                            <select class="form-control" id="members_id" name="members_id">
                              <option value="">Select Team Members</option>
                                @foreach ($support_team as $id =>$mem_name)
                                  <option value="{{ $id  }}" {{ request('members_id') == $id ? 'selected' : '' }}>{{ $mem_name }}</option>
                                @endforeach
                            </select>
                      </div>
                      
                      <div class="form-group col-md-2 d-flex align-items-end" style="margin: 23px 0 0 0;">
                        <button type="submit" class="btn btn-primary w-100">Generate</button>
                      </div>
                    <div class="form-group col-md-2" style="margin: 23px 0 0 0;">
                          <button class="btn btn-primary download_tasks">Export to excel 
                            <i class="fa fa-download" aria-hidden="true"></i>
                          </button>
                    </div>
                    </div>
                  </form>
                  <table id="ticketReportTable" class="table table-bordered">
                    <thead>
                      <tr>
                        <th>S.no</th>
                        <th>Ticket Name</th>
                        <th>Ticket Type</th>
                        <th>Problem Type</th>
                        <th>Employee Name</th>
                        <th>Ticket Created Date</th>
                        <th>Ticket Status</th>
                        <th>Assigned Members</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
              
          </div>
           

            </div>
        </div>

     
   <script>
    $("#start_date").datepicker({
        dateFormat: "dd-mm-yy",
        onSelect: function(selectedDate) {
            var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate);
            var minDate = new Date(dateObj);
            minDate.setDate(minDate.getDate() + 1);
            $("#end_date").datepicker("option", "minDate", minDate);
        }
    });

    $("#end_date").datepicker({
        dateFormat: "dd-mm-yy"
    });
$(document).ready(function () {
let table = $('#ticketReportTable').DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "{{ route('ticket_report_action') }}",
    type: "POST",
    data: function(d) {
      d._token = "{{ csrf_token() }}";
      d.start_date = $('#start_date').val();
      d.end_date = $('#end_date').val();
      d.members_id = $('#members_id').val();
      d.ticket_status = $('#ticket_status').val();
    }
  },
  columns: [
    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
    { data: 'ticket_name', name: 'ticket_name' },
    { data: 'ticket_type', name: 'ticket_type' },
    { data: 'problem_type', name: 'problem_type' },
    { data: 'ticket_owner', name: 'ticket_owner' },
    { data: 'created_date', name: 'created_date' },
    { data: 'ticket_status', name: 'ticket_status' },
    { data: 'assigned_members', name: 'assigned_members', orderable: false, searchable: false }
  ]
});
});
  $('#AttendanceReport').on('submit', function(e) {
  e.preventDefault(); // Prevent default form submission
  $('#ticketReportTable').DataTable().ajax.reload(); // Reload DataTable with new filters
});
   $('.download_tasks').on('click',function(){
    const start =  $('#start_date').val();
    const end = $('#end_date').val();
    const ticket_status = $('#ticket_status').val();
    const members_id = $('#members_id').val();
    const downloadUrl = `/ticket_report_export?start_date=${start}&end_date=${end}&ticket_status=${ticket_status}&members_id=${members_id}`;
   // alert(downloadUrl);
    window.location.href = downloadUrl;
    });
</script>

<style>
  .form-control:disabled, .form-control:read-only {
    background-color: white;
  }
</style>   
          @endsection 