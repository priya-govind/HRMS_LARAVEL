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
        <div class="card">
            <div class="card-header">
                <div class="float-start">
                    List All Chat Bot Menus
                </div>
                <div class="float-end">
                    <button type="button" id="addButton" class="btn btn-primary btn-sm">+Add New Chat Bot Menu
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
                                <th>Bot Menu Name</th>
                                <th>Parent Category</th>
                                <th>Command</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody> </tbody>
                        </table>
                    </div>
                </div>
          </div>
               <!-- Modal -->
               <!-- Modal -->
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
                        <label for="name" class="form-label">Menu Name</label>
                        <input type="text" class="form-control" id="bot_name" name="bot_name" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Command</label>
                        <input type="text" class="form-control" id="command" name="command">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Select Parent Category</label>
                        <select name="parent_id" id="parent_id" class="form-control form-control-sm parentCheckbox">
                            <option value="">Select </option>
                            <option value='1'>Parent </option>
                            @foreach ($categories_parent as $cat )
                            <option value="{{ $cat->id }}">{{ $cat->bot_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="service_info" style="display:none;">
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name</label>
                        <input type="text" class="form-control" id="service_name" name="service_name">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Method</label>
                        <input type="text" class="form-control" id="service_method" name="service_method">
                    </div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Menu Status:</label>&nbsp;&nbsp;
                        <input type="radio" class="form-check-input" id="is_active" name="is_active" value="1" required> Active &nbsp;&nbsp;
                        <input type="radio" class="form-check-input" id="is_active" name="is_active" value="0" required>Inactive
                    </div>
                    <div class="mb-3">
                        <input type="checkbox" name="support_access" id="support_access" value="1">
                        <label for="name" class="form-label">Allow Support Team only</label>
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
                ajax: "{{ route('bot_menus.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex' },
                    { data: 'bot_name', name: 'bot_name' },
                    { data: 'parent_name',name:'parent_name'},
                    { data: 'command',name:'command'},
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 25,
            });
        });
</script>
<script src="{{ asset('assets/js/common.js') }}"></script>
<script src="{{ asset('assets/js/bot_menus.js') }}"></script>
@endsection