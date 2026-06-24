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

          <div class="row">
              <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-header">
                        <div class="float-start">
                            Raise Ticket
                        </div>
                    </div> 
                  <div class="card-body">
                          <table  id="ticketTable" class="display table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sno</th>
                                        <th>Ticket Type</th>
                                        <th>Problem Type</th>
                                        <th>Ticket Name</th>
                                        <th>Ticket Status </th>
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
                        <label for="name" class="form-label">Select Ticket Type</label>
                        <select name="ticket_type_id" id="ticket_type_id" class="form-control form-control-sm">
                            <option value="">Select </option>
                            @foreach ($ticket_type as $t_type )
                            <option value="{{ $t_type->id }}">{{ $t_type->ticket_type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Problem Type</label>
                        <select name="problem_type_id" id="problem_type_id" class="form-control form-control-sm">
                          <option value="">Select </option>
                            @foreach ($problem_type as $p_type )
                            <option value="{{ $p_type->id }}">{{ $p_type->problem_type }}</option>
                            @endforeach
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="name" class="form-label">Ticket Name</label>
                        <input type="text" class="form-control" id="ticket_name" name="ticket_name" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Description about Ticket</label>
                        <textarea class="form-control" id="ticket_desc" name="ticket_desc"> </textarea>
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
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 10,
            });
        });
</script>
<script src="{{ asset('assets/js/common.js') }}"></script>
<script src="{{ asset('assets/js/tickets.js') }}"></script>
          @endsection
         