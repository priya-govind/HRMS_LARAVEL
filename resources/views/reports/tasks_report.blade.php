@extends('layouts.app')
@section('content')
@include('layouts.includes.topbar')
<div class="container-fluid page-body-wrapper">
  @include('layouts.includes.sidebar')
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row justify-content-center mt-4">
        <div class="col-md-12">
          <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
              <h5 class="mb-0">Tasks Report</h5>
            </div>
            <div class="card-body">
              <form method="POST" id="TasksReport">
                @csrf
           <div class="row mb-3" 
                {{ !in_array(session('role_id'), config('global.task_monitor_roles')) ? 'style=display:none;' : '' }}>
              <div class="col-md-2">
                <input type="radio" name="status_type" value="1" {{ $status['self'] }}> Your Tasks
              </div>
              <div class="col-md-2">
                <input type="radio" name="status_type" value="2" {{ $status['other'] }}> Employee's Tasks
              </div>
            </div>
                <div class="row">
                  <div class="form-group col-md-1">
                    <label>Start Date</label>
                   <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="{{ request('start_date') }}"  readonly>
                  </div>
                  <div class="form-group col-md-1">
                    <label>End Date</label>
                     <input type="text" class="form-control" id="end_date" name="end_date" placeholder="End Date" value="{{ request('end_date') }}"  readonly>
                  </div>
                  <div class="form-group col-md-2" id="project_filter">
                    <label>Project</label>
                    <select name="project_id" id="project_id" class="form-control">
                      <option value="">Select Project</option>
                      @foreach ($projects as $proj)
                      <option value="{{ $proj->id }}">{{ $proj->proj_name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-md-2" id=module_filter">
                    <label>Module</label>
                    <select name="module_id" id="module_id" class="form-control">
                      <option value="">Select Module</option>
                    </select>
                  </div>
                  <div class="form-group col-md-2" id="employee_filter"    @if(!in_array(session('role_id'), config('global.all_in_all_access'))) style="display:none;"  @endif>
                    <label>Employee</label>
                    <select name="emp_id" id="emp_id" class="form-control">
                      <option value="">Select Employee</option>
                      @foreach ($employees as $emp)
                      <option value="{{ $emp->id }}">{{ $emp->name }} - {{ $emp->roles->first()->role_name }}</option>
                      @endforeach
                    </select>
                  </div>
                   <div class="form-group col-md-2" id="employee_filter"    @if(!in_array(session('role_id'), config('global.all_in_all_access'))) style="display:none;margin: 23px 0 0 0;" @else style="margin: 23px 0 0 0;"  @endif>
                      <button type="submit" class="btn btn-primary w-100">Generate</button>
                  </div>
                    <div class="form-group col-md-2" style="margin: 23px 0 0 0;">
                      <button class="btn btn-primary download_tasks">Export to excel 
                        <i class="fa fa-download" aria-hidden="true"></i>
                      </button>
                    </div>
                </div>
              </form>

              <table id="taskReportTable" class="table table-bordered">
                <thead>
                  <tr>
                    <th>S.no</th>
                    <th>Overall Task Name</th>
                    <th>Project</th>
                    <th>Employee Name</th>
                    <th>Deadline Date</th>
                    <th>Individual Task Status</th>
                    <th>Overall Task Status</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>

            </div>
          </div>
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
  const table = $('#taskReportTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "{{ route('tasks_report_action') }}",
      type: "POST",
        data: function (d) {
          d._token = "{{ csrf_token() }}";
          d.status_type = $("input[name='status_type']:checked").val();
          d.project_id = $('#project_id').val();
          d.start_date = $('#start_date').val();
          d.end_date = $('#end_date').val(); 
          if (d.status_type == "2") {
            d.emp_id = $('#emp_id').val();
          }
        }
    },
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'over_all_task', name: 'over_all_task' },
      { data: 'proj_name', name: 'proj_name' },
      { data: 'emp_name', name: 'emp_name' },
      { data: 'task_end_date', name: 'task_end_date' },
      { data: 'emp_task_status', name: 'emp_task_status' },
      { data: 'overall_status', name: 'overall_status' }
    ]
  });
  
    $("#TasksReport").validate({
        errorClass: "is-invalid", 
        rules: {
            start_date: {
                required: true,
            },
            end_date: {
                required: true,
            },
        },
        messages: {
            start_date: {
                required: "Start Date is Required.",
            },
            end_date: {
                required: "End Date is Required.",
            },
        },
        highlight: function (element, errorClass) {
            $(element).addClass(errorClass); // Highlight invalid fields
        },
        unhighlight: function (element, errorClass) {
            $(element).removeClass(errorClass); // Remove highlight from valid fields
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent()); // Place error message next to the field
        },
        submitHandler: function (form) {
         table.ajax.reload(null, false);
        },
    });

  $("input[name='status_type']").change(function () {
    const selected = $(this).val();
    if (selected == "2") {
      $('#employee_filter').show();
    } else {
      $('#employee_filter').hide();
      $('#emp_id').val('');
    }
  });
   $('.download_tasks').on('click',function(){
    const start =  $('#start_date').val();
    const end = $('#end_date').val();
    const project = $('#project_id').val();
    const emp_id = $('#emp_id').val();
    const status_type=$("input[name='status_type']:checked").val();
    const module_id=$('#module_id').val();
    const downloadUrl = `/export-tasks?start_date=${start}&end_date=${end}&project_id=${project}&emp_id=${emp_id}&status_type=${status_type}&module_id=${module_id}`;
   // alert(downloadUrl);
    window.location.href = downloadUrl;
    });
        $('#project_id').change(function() {
              var proj_id = $(this).val();
              if(proj_id) {
                  $.ajax({
                      url: '/get_project_modules/' + proj_id,
                      type: 'GET',
                      success: function(data) {
                          $('#module_id').empty();
                          $('#module_id').append('<option value="">Select Module</option>');
                          $.each(data, function(id, name) {
                              $('#module_id').append('<option value="' + id + '">' + name + '</option>');
                          });
                           
                      }
                  });
              } else {
                  $('#module_id').empty();
                  $('#module_id').append('<option value="">Select Module</option>');
              }
        });
          $('#module_id').change(function() {
            var type = $(this).val();
                var module_id = $('#module_id').val();
                if(module_id) {
                    $.ajax({
                        url: '/get_assign_proj_members/' + module_id,
                        type: 'GET',
                            data: {
                              module_id: module_id,
                                },
                        success: function(data) {
                            $('#emp_id').empty();
                          // $('#emp_id').append('<option value="">Select Employees</option>');
                            $.each(data, function(id, name) {
                                $('#emp_id').append('<option value="' + id + '">' + name + '</option>');
                            });
                           // $('#emp_id').multiselect('reload');
                        }
                    });
                } else {
                    $('#emp_id').empty();
                    $('#emp_id').append('<option value="">Select Employee</option>');
                }
            });
});
</script>
@endsection