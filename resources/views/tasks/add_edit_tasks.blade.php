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
          <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                  <nav class="p-1">
                  <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="{{ route('tasks.proj_tasks') }}">Tasks</a></li>
                      <li class="breadcrumb-item active">Assign Task</li>
                  </ol>
              </nav>
                    <h4 class="card-title">Assign Tasks</h4>
                    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

                    <div id="success-message1" class="alert alert-success"  role="alert"  style="display: none;"></div>
                    <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                    <form id="dataForm" method="post" 
                                    action="{{ isset($task->id) ? route('tasks.update', $task->id) : route('tasks.store') }}" 
                                    enctype="multipart/form-data">
                                    @csrf
                                   

                      <div class="form-group">
                        <label for="exampleInputUsername1">Task Name</label>
                        <div class="col-6">
                        <input type="text" class="form-control" id="task_name" placeholder="Task Name" name="task_name"  value="{{ old('task_name', $task->task_name) }}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">End Date</label>
                        <div class="col-6">
                        <input type="text" class="form-control"  id="endDate" name="endDate" placeholder="End Date"  value="{{ old('endDate', $task->endDate) }}" readonly>
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">Select Project Name</label>
                        <div class="dropdown col-6">
                        <select name="project_id" id="project_id" class="form-control frm_ctrl_select parentCheckbox">
                          <option value="">Select</option>
                          @foreach($projects as $proj)
                              <option value="{{ $proj->id }}" {{ old('project_id', $task->project_id) == $proj->id ? 'selected' : '' }}>{{ $proj->proj_name }}</option>
                          @endforeach
                      </select>
                       </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">Select Module Name</label>
                        <div class="dropdown col-6">
                        <select name="module_id" id="module_id" class="form-control frm_ctrl_select parentCheckbox">
                          <option value="">Select</option>
                      </select>
                       </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">Task Description</label>
                        <div class="dropdown col-6">
                        <textarea name="task_desc" id="task_desc" class="form-control">{{ old('task_desc', $task->task_desc) }}</textarea>
                       </div>
                      </div>
                      <div class="form-group">
                          <label for="exampleInputUsername1">Select Employees to Assign</label>
                          <div class="dropdown col-6">
                              <select name="emp_id[]" id="emp_id" class="frm_ctrl_select" multiple>
                                  <option  value="">Select</option>
                                  
                              </select>
                          </div>
                          <div id="error_emp_id" class="error-text" style="display: none;"></div>
                           <div id="error_emp_id_msg" class="error-text" style="display: none;color:red;"></div>
                      </div>
                      <div class="form-group">
                          <label for="exampleInputUsername1">Upload Files:</label>
                          <div class="dropdown col-6">
                               <input type="file" name="files[]" multiple><br/>
                               <span style="color:red;">Multiple files can be uploaded.<br/> Press Control button to select multiple files.</span>
                          </div>
                      </div>
                      @if( isset($task->id) && $docs->isNotEmpty())
                        <div class="row">
                            <div class="col-md-6 uploaded-docs-section">
                                Uploaded Documents:<br/>
                                <table class="table">
                                    @foreach($docs as $indiv_docs)
                                        <tr id="row-{{ $indiv_docs->id}}">
                                            <td>{{ $indiv_docs->original_name }}</td>
                                            <td>
                                                <a href="{{ asset('task_uploads/' . $indiv_docs->stored_name) }}" class="btn btn-primary btn-sm" download="{{$indiv_docs->stored_name}}" title="Download">
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                               &nbsp; | &nbsp; <button data-id="{{ $indiv_docs->id}}" class="btn btn-danger btn-sm remove_doc"><i class="fa fa-trash"  title="Delete"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        @endif
                      <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                    </form>
                  </div>
                </div>
              </div>
          </div>

<script type="text/javascript">
$(document).ready(function() {

$('#emp_id').multiselect
({
    columns: 1,
    texts: {
        placeholder: 'Select Employees to Assign',
        search     : 'Search'
    },                                
    search: true,
    afterSelect: function() {
                $('#emp_id').valid(); // Trigger validation on select
    },
    afterDeselect: function() {
            $('#emp_id').valid(); // Trigger validation on deselect
    },
        onOptionClick:function(element, option) {
            validateSelection(element, "error_emp_id_msg"); // Call validation function
        },

});
    $('#module_id').attr('style','pointer-events:none;').css('background-color:#ccc');
        var proj_id = $("#project_id").val();
        var selectedModuleId = "{{ $task->module_id ?? '' }}";
        var selectedEmpIds   = @json($assignedEmployees ?? []);

        if(proj_id) {
            $.ajax({
                url: '/get_project_modules/' + proj_id,
                type: 'GET',
                success: function(data) {
                    $('#module_id').empty().append('<option value="">Select Module</option>');
                    $.each(data, function(id, name) {
                        var selected = (id == selectedModuleId) ? 'selected' : '';
                        $('#module_id').append('<option value="'+id+'" '+selected+'>'+name+'</option>');
                    });
                }
            });
        }
        if(selectedModuleId){
            $.ajax({
                url: '/get_assign_proj_members/' + selectedModuleId,
                type: 'GET',
                success: function(data) {
                    $('#emp_id').empty();
                    $.each(data, function(id, name) {
                        // check if this employee is already assigned
                        var selected = selectedEmpIds.includes(parseInt(id)) ? 'selected' : '';
                        $('#emp_id').append('<option value="' + id + '" ' + selected + '>' + name + '</option>');
                    });
                    $('#emp_id').multiselect('reload');
                }
            });
        }
//    $("#startDate").datetimepicker({
//     dateFormat: "dd-mm-yy",
//     timeFormat: "HH:mm:ss",
//      minDate: 0,
//     onSelect: function(selectedDate) {
//         var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate); // Parse selected date
//         var minDate = new Date(dateObj);
//         minDate.setDate(minDate.getDate() + 1); // Ensure endDate is at least 1 day ahead
//         $("#endDate").datetimepicker("option", "minDate", minDate);
//     }
// });
        $("#endDate").datepicker({
            dateFormat: "dd-mm-yy",
            timeFormat: "HH:mm:ss",
             minDate: 0,
        });
   


    // Add a custom validation method for regex pattern
    $.validator.addMethod("pattern", function (value, element, regex) {
        return this.optional(element) || new RegExp(regex).test(value);
    }, "Invalid input.");


// $.validator.addMethod("validDateTime", function(value, element) {
//     return this.optional(element) || /^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}$/.test(value);
// }, "Please enter a valid date-time format (DD-MM-YYYY HH:MM:SS).");
$.validator.addMethod("validDate", function(value, element) {
    return this.optional(element) || /^\d{2}-\d{2}-\d{4}$/.test(value);
}, "Please enter a valid date format (DD-MM-YYYY).");

// Custom filesize rule
$.validator.addMethod("filesize", function (value, element, param) {
  if (element.files.length === 0) return true;
  for (let i = 0; i < element.files.length; i++) {
    if (element.files[i].size > param) return false;
  }
  return true;
}, "File too large.");

function parseDate(dateStr) {
    var parts = dateStr.split(" "); // Separate date and time
    var dateParts = parts[0].split("-"); // Split date part (dd-mm-yyyy)

    return new Date(dateParts[2], dateParts[1] - 1, dateParts[0], ...parts[1].split(":"));
}

 function validateCheckboxGroup(name, errorId, msg) {
    let checkboxes = $('input[name="' + name + '"]');
    let isChecked = checkboxes.is(":checked");
    let errorContainer = $("#" + errorId);
// alert(name+'=='+length);
    // if (!isChecked) {
    //     errorContainer.html("<span style='color:red;'>Please select at least one option.</span>").fadeIn();
    //     return false;
    // } else {
    //     errorContainer.fadeOut();
    //     return true;
    // }
}
$('.remove_doc').click(function(e){
    e.preventDefault();
    const itemId = $(this).data('id');
    $.ajax({
        url: '/remove_attachment/' + itemId,
        type: 'GET',
        success: function(data) {
            // Remove the row
            $('#row-' + data.row_id).remove();

            // Check if any rows remain in the table
            if ($('.table tr').length === 0) {
                // Remove the whole Uploaded Documents section
                $('.uploaded-docs-section').remove();
                // Or show a message instead:
                // $('.uploaded-docs-section').html('<p>No documents uploaded.</p>');
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON.message);
        }
    });
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
                    $('#emp_id').multiselect('reload');
                }
            });
        } else {
            $('#emp_id').empty();
            $('#emp_id').append('<option value="">Select Employee</option>');
        }
    });
    //  $('#module_id').click(function () {
    //        let spanText = $(".test1 button span").text().trim(); // Get span text and remove extra spaces

    //         if (spanText.length === 0 || spanText=='Select') {
    //            // $("#error_emp_id").html("<span style='color:red;'></span>").fadeIn();
    //             $("#error_emp_id_msg").show().append("Please select at least one option.");

    //     setTimeout(function() {
    //         $("#error_emp_id_msg").fadeOut();
    //     }, 30000);
    //         } else {
    //             $("#error_emp_id_msg").fadeOut();
    //         }
    //  });

    // Initialize validation
    $("#dataForm").validate({
        errorClass: "is-invalid", // Bootstrap class for highlighting errors
        rules: {
            task_name: {
                required: true,
                pattern: /^[a-zA-Z0-9\s]*$/,
            },
            // startDate: {
            //     required: true,
            //     validDate: true
            // },
            endDate: {
                required: true,
                validDate: true
            },
            project_id: {
                required: true,
            },
            module_id: {
                required: true,
            },
            task_desc:{
                required:true,
            },
            "emp_id[]": {
                required: function(element) {
                // validateCheckboxGroup("emp_id[]", "error_emp_id",'step5');
                return $(element).val() === null || $(element).val().length === 0;
                },
            },
           "files[]": {
                filesize: 5242880
            }
        },
        messages: {
            task_name: {
                required: "Task name is required.",
                pattern: "Special characters are not allowed in the Task Name.",
            },
            // startDate: {
            //     required: "Please select a valid start date.",
            // },
            endDate: {
                required: "Please select a valid end date.",
            },
            project_id: {
                required: "Please select a Project Name.",
            },
            module_id: {
                required: "Please select a Module Name.",
            },
            task_desc:{
                required:"Please Enter Task Description.",
            },
            "emp_id[]": {
                required: "Please select at least one Team Type.",
            },
            "files[]": {
                extension: "Allowed file types: jpg, jpeg, png, pdf, doc, docx, xls, xlsx",
                filesize: "Each file must be less than 5 MB"
            }
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
    });
          $('#project_id').change(function() {
          $('#module_id').attr('style','pointer-events:visible;');
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
          $('#emp_id option').on('mousedown', function () {
    lastClickedIndex = this.index;
});
function validateSelection(element, errorId) {
    var selectedValues = $(element).val() || [];

    if (selectedValues.length === 0) {
        $("#" + errorId).append("Please select at least one option.").fadeIn();
    } else {
        $("#" + errorId).fadeOut();
    }
}
      });
</script>  
        @endsection      