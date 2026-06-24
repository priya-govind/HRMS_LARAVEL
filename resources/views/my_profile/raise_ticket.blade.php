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
.dataForm {
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
    color: 215dab;
    background-color: white !important;
    border: 1px solid #215dab;
    outline: 1px solid #ced4da;
    width: 75%;
    border-radius: 3px;
    appearance: auto; /* For most modern browsers */
    -webkit-appearance: auto; /* Safari/Chrome */
    -moz-appearance: auto; /* Firefox */

}
.input-fields{
        background-color: white !important;
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
                    Tickets Information
                </div>              
            </div> 
                  <div class="card-body">
                    <div class="row">
                <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                <div class="col-md-12 raise_ticket">
                <form id="dataForm" class="dataForm" method="post" novalidate="novalidate">
                    @csrf                
                    <h5><b> Raise New Ticket </b></h5><br>
                      <div class="row">
                          <div class="col-md-3 mb-3">
                              <label for="name" class="form-label">Select Ticket Type</label>
                                <select name="ticket_type_id" id="ticket_type_id" class="form-control form-control-sm">
                                    <option value="">Select </option>
                                    @foreach ($ticket_type as $t_type )
                                    <option value="{{ $t_type->id }}">{{ $t_type->ticket_type }}</option>
                                    @endforeach
                                </select>
                          </div>
                          <div class="col-md-3 mb-3">
                              <label for="name" class="form-label">Problem Type</label>
                                    <select name="problem_type_id" id="problem_type_id" class="form-control form-control-sm">
                                    <option value="">Select </option>
                                        @foreach ($problem_type as $p_type )
                                        <option value="{{ $p_type->id }}">{{ $p_type->problem_type }}</option>
                                        @endforeach
                                    </select>
                          </div>
                      </div>
                          <div class="row">
                          <div class="col-md-2 mb-3">
                              <label for="name" class="form-label">Ticket Name</label>
                              <input type="text" class="form-control" id="ticket_name" name="ticket_name" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed">
                          </div>
                      </div>
                    <div class="col-md-4 mb-3">
                        <label for="name" class="form-label">Description about Ticket</label>
                        <textarea id="ticket_desc" name="ticket_desc"> </textarea>
                    </div>
                  <button type="submit" class="btn btn-primary" id="saveBtn">Submit</button>
                </form>
                </div>
            </div>
            <h5><b> Raised Tickets </b></h5><br>
                          <table  id="ticketTable" class="display table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sno</th>
                                        <th>Ticket Type</th>
                                        <th>Problem Type</th>
                                        <th>Ticket Name</th>
                                        <th>Ticket Status </th>
                                         @if(session('support_access')==true)
                                         <th>Ticket Created by </th>
                                        <th>Action</th>
                                        @endif
                                    </tr>
                                </thead>
                              <tbody>
                              
                              </tbody>
                          </table>
                  </div>
                </div>
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
                            <form id="viewForm">
                                @csrf
                                <input type="hidden" id="recordId" name="id">
                                <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Select Ticket Type</label>
                                            <select name="ticket_type_id_edit" id="ticket_type_id_edit" class="form-control form-control-sm">
                                                <option value="">Select </option>
                                                @foreach ($ticket_type as $t_type )
                                                <option value="{{ $t_type->id }}">{{ $t_type->ticket_type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Select Problem Type</label>
                                            <select name="problem_type_id_edit" id="problem_type_id_edit" class="form-control form-control-sm">
                                            <option value="">Select </option>
                                                @foreach ($problem_type as $p_type )
                                                <option value="{{ $p_type->id }}">{{ $p_type->problem_type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Ticket Name</label>
                                            <input type="text" class="form-control input-fields" id="ticket_name_edit" name="ticket_name_edit" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed">
                                        </div>
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Description about Ticket</label>
                                            <textarea class="form-control input-fields" id="ticket_desc_edit" name="ticket_desc_edit" style="margin: 0 0 0 5%;position: absolute;"> </textarea>
                                        </div>
                                </div><br/>
                                <div class="row assignedMember">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Assigned Members for the ticket:</label>
                                            <ul id="assignedMembersList">
                                                <li>
                                                </li>
                                            </ul>
                                        </div>
                                </div>
                                 <div class="mb-3" id="reply_to" style="display: none;">
                                            <label for="name" class="form-label">Comments  After Ticket Solved:</label>
                                            <textarea class="form-control" id="ticket_reply_edit" name="ticket_reply_edit" style="margin: 0 0 0 5%;position: absolute;"> </textarea>
                                        </div>
                                <button type="button" class="btn btn-danger" style="margin: 9px 0 0 0;" data-bs-dismiss="modal">Close</button>
                            </form>
                        </div>
                    </div>
                </div>
        </div> 
        <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content logic_cont">
                        <div class="modal-header">
                            <h5 class="modal-title" id="assignModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="success-message" class="alert alert-success" role="alert" style="display: none;"></div>
                            <div id="error-message" class="alert alert-danger" style="display: none;"></div>
                            <form id="AssignForm">
                                @csrf
                                <input type="hidden" id="hid_id" name="hid_id">
                                <input type="hidden" name="ownerId" id="ownerId">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="name" class="form-label">Assign Ticket to:</label>
                                        <select name="assign_mem_id[]" id="assign_mem_id" class="form-control form-control-sm" multiple>
                                            @foreach ($support as $s_mem )
                                            <option value="{{ $s_mem->id }}">{{ $s_mem->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label for="name" class="form-label">Comments:</label>
                                       <textarea name="assign_comments" id="assign_comments"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
        </div>
        
        
        <div class="modal fade" id="UpdateModal" tabindex="-1" aria-labelledby="UpdateModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content logic_cont">
                    <div class="modal-header">
                        <h5 class="modal-title" id="UpdateModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                        <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                        <form id="UpdateTicketForm"  method="post" action="{{ route('ticket_ind_update') }}" >
                            @csrf
                            <!-- @method('PUT') -->
                            <input type="hidden" name="ticket_id" id="ticket_id">
                            <div class="mb-3">
                                <label for="name" class="form-label">Ticket Name</label>
                                <input type="text" class="form-control" id="ticket_name_update" name="ticket_name" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Ticket Status:</label>&nbsp;&nbsp;
                                <select name="ticket_status" id="ticket_status" class="frm_ctrl_select">
                                    <option  value="">Select</option>
                                    @foreach ($project_status as $status)
                                        <option value="{{ $status->id }}">{{ $status->proj_status_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Comments</label>
                            <textarea name="reply_to" id="reply_to_update"  class="form-control"></textarea>
                            </div>
                            <input type="submit" class="btn btn-primary" id="updateBtn" name="Save" value="Save">
                            <button type="button" class="btn btn-danger" style="margin: 9px 0 0 0;" data-bs-dismiss="modal">Close</button>
                        </form>
                    </div>
                </div>
            </div>
        </div> 


@include('layouts.includes.delete_popup')
<script type="text/javascript">
    $(document).ready(function () {
        $('#ticketTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('tickets.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'ticket_type', name: 'ticket_type' },
                    { data: 'problem_type', name: 'problem_type' },
                    { data: 'ticket_name', name: 'ticket_name' },
                    { data: 'ticket_status', name: 'ticket_status'},
                     @if(session('support_access')==true)
                      { data: 'ticket_owner', name: 'ticket_owner'},
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                    @endif
                ],
                pageLength: 10,
            });
        });
        @if(session('support_access')==true)
        $('.raise_ticket').hide();
        @endif
</script>
<script src="{{ asset('assets/js/common.js') }}"></script>
<script src="{{ asset('assets/js/tickets.js') }}"></script>
          @endsection
         