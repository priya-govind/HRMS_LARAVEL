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
                      <li class="breadcrumb-item"><a href="{{ route('tasks.manage_tasks') }}">Tasks</a></li>
                      <li class="breadcrumb-item active">Assign Task</li>
                  </ol>
              </nav>
                    <h4 class="card-title">Assign Tasks</h4>
                    <div id="success-message1" class="alert alert-success"  role="alert"  style="display: none;"></div>
                    <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                    <form id="dataForm" method="post" action="{{route('create_task')}}">
                      @csrf
                      <div class="form-group">
                        <label for="exampleInputUsername1">Task Name</label>
                        <div class="col-6">
                        <input type="text" class="form-control" id="task_name" placeholder="Task Name" name="task_name">
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">Start Date</label>
                        <div class="col-6">
                        <input type="text" class="form-control" id="startDate" name="startDate" placeholder="Start Date" readonly>
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">End Date</label>
                        <div class="col-6">
                        <input type="text" class="form-control"  id="endDate" name="endDate" placeholder="End Date" readonly>
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">Select Project Type</label>
                        <div class="dropdown col-6">
                        <select name="proj_typ_id" id="proj_typ_id" class="form-control frm_ctrl_select parentCheckbox">
                          <option value="">Select</option>
                          @foreach($project_type as $proj_typ)
                              <option value="{{ $proj_typ->id }}">{{ $proj_typ->proj_typ_name }}</option>
                          @endforeach
                      </select>
                       </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">Select Project Name</label>
                        <div class="dropdown col-6">
                        <select name="proj_id" id="proj_id" class="form-control frm_ctrl_select parentCheckbox">
                          <option value="">Select</option>
                      </select>
                       </div>
                      </div>
                      <div class="form-group">
                          <label for="exampleInputUsername1">Select Team Type</label>
                          <div class="dropdown col-6">
                              <select name="team_type[]" id="team_type" class="frm_ctrl_select" multiple>
                                  <option  value="">Select</option>
                                  @foreach($team_type as $tem_typ)
                                      <option value="{{ $tem_typ->id }}">{{ $tem_typ->team_typ_name }}</option>
                                  @endforeach
                              </select>
                          </div>
                          <div id="error_team_type" class="error-text" style="display: none;"></div>
                           <div id="error_team_type_msg" class="error-text" style="display: none;color:red;"></div>
                      </div>
                      <div class="form-group">
                          <label for="exampleInputUsername1">Select Team Name</label>
                          <div class="dropdown col-6">
                              <select name="team_ids[]" id="team_ids" class="frm_ctrl_select"  multiple>
                                  <option  value="">Select</option>
                              </select>
                          </div>
                          <div id="error_team_name" class="error-text" style="display: none;"></div>
                      </div>
              <div class="form-group">
                <div class="row">
                    <div class="col-md-7">
                        <label for="team_members_ids">Select Team Members</label>
                        <div class="dropdown">
                            <select name="team_members_ids[]" id="team_members_ids" class="frm_ctrl_select form-control" multiple>
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div id="error_team_member" class="error-text" style="display: none;"></div>
                    </div>
                    <div class="col-md-4 mt-4" id="generate_tool" style="display:none;">
                        <button name="generate_task" id="generate_task" class="btn btn-danger blinking" type="button">Generate Task for Each Member</button>
                    </div>
                </div> 
            </div>
            <div class="form-group" id="assign_task_id" style="display:none;">
                <div class="row col-md-7">
                    Assign Tasks Individually
                     <table class="table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Task</th>
                            </tr>
                        </thead>
                        <tbody id="memberTaskRows">
                            
                        </tbody>
                    </table>

                
                </div>
            </div>
                      <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                    </form>
                  </div>
                </div>
              </div>
          </div>

<script type="text/javascript">
$(document).ready(function() {

$('#team_type').multiselect
({
    columns: 1,
    texts: {
        placeholder: 'Select Team Type',
        search     : 'Search'
    },                                
    search: true,
    afterSelect: function() {
                $('#team_type').valid(); // Trigger validation on select
    },
    afterDeselect: function() {
            $('#team_type').valid(); // Trigger validation on deselect
    },
        onOptionClick:function(element, option) {
            validateSelection(element, "error_team_type_msg"); // Call validation function
        },

});
$('#team_ids').multiselect
({
    columns: 1,
    texts: {
        placeholder: 'Select Team Type',
        search     : 'Search'
    },
    search: true,
       afterSelect: function() {
                $('#team_ids').valid(); // Trigger validation on select
        },
        afterDeselect: function() {
                $('#team_ids').valid(); // Trigger validation on deselect
        },
        onOptionClick:function(element, option) {
            validateSelection(element, "error_team_name"); // Call validation function
        },
  
});
$('#team_members_ids').multiselect
({
    columns: 1,
    texts: {
        placeholder: 'Select Team Type',
        search     : 'Search'
    },
    search: true,
    afterSelect: function() {
            $('#team_members_ids').valid(); // Trigger validation on select
    },
    afterDeselect: function() {
            $('#team_members_ids').valid(); // Trigger validation on deselect
    },
    onOptionClick: function(element, option) {
        validateSelection(element, "error_team_member"); // Call validation function
    },
    
});



       $('#proj_id').attr('style','pointer-events:none;').css('background-color:#ccc');
    //    $('#team_type').attr('style', 'pointer-events: none;').css('background-color', '#ccc');
    //    $('#team_ids').attr('style','pointer-events:none;').css('background-color:#ccc');
    //    $('#team_members_ids').attr('style','pointer-events:none;').css('background-color:#ccc');

 $('#generate_task').click(function() {
    $("#memberTaskRows").empty(); // Clear previous entries
    let selectedMembers = $("#team_members_ids").val(); // Get selected values


    // Split each selected value and keep only the first part before "--"
    let processedMembers = selectedMembers.map(member => String(member).split("--")[0]);
    let processedTeams = $("#team_ids").val();
// let processedTeams = selectedMembers.map(member => String(member).split("--")[1]);
    $.ajax({
        url: `/tasks/get-assigned-members`,
        type: "GET",
        data: { members: processedMembers,
                teams:processedTeams,
         }, // Sending only extracted values
        success: function(response1) {
            
            response1.forEach(function(member) {
               
             var readonlyAttr = (member.team_owner !== 'own') ? 'readonly' : '';
             var tdClass = (member.team_owner !== 'own') ? 'class="form-control disabledsingle-field"' : 'class="form-control"';
             
            $("#memberTaskRows").append(`
                    <tr>
                        <td><input type="hidden" name="assign_mem_id[]" value="${member.id}" ${readonlyAttr}> ${member.name} - ${member.team_name}</td>
                        <td><input type="text" ${tdClass} name="assign_mem_task[${member.id}]" value="${member.task_info}" placeholder="Task for ${member.name}" ${readonlyAttr}></td>
                    </tr>
                `);
            });

            $('#assign_task_id').show(); 
            $('#generate_tool').hide(); 
            $('#saveBtn').prop('disabled', false);              
        }
    });
});
   $("#startDate").datetimepicker({
    dateFormat: "dd-mm-yy",
    timeFormat: "HH:mm:ss",
     minDate: 0,
    onSelect: function(selectedDate) {
        var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate); // Parse selected date
        var minDate = new Date(dateObj);
        minDate.setDate(minDate.getDate() + 1); // Ensure endDate is at least 1 day ahead
        $("#endDate").datetimepicker("option", "minDate", minDate);
    }
});

        $("#endDate").datetimepicker({
            dateFormat: "dd-mm-yy",
            timeFormat: "HH:mm:ss"
        });
   


    // Add a custom validation method for regex pattern
    $.validator.addMethod("pattern", function (value, element, regex) {
        return this.optional(element) || new RegExp(regex).test(value);
    }, "Invalid input.");


$.validator.addMethod("validDate", function(value, element) {
    return this.optional(element) || /^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}$/.test(value);
}, "Please enter a valid date-time format (DD-MM-YYYY HH:MM:SS).");

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

     $('#proj_id').click(function () {
           let spanText = $(".test1 button span").text().trim(); // Get span text and remove extra spaces

            if (spanText.length === 0 || spanText=='Select') {
               // $("#error_team_type").html("<span style='color:red;'></span>").fadeIn();
                $("#error_team_type_msg").show().append("Please select at least one option.");

        setTimeout(function() {
            $("#error_team_type_msg").fadeOut();
        }, 30000);
            } else {
                $("#error_team_type_msg").fadeOut();
            }
     });

    // Initialize validation
    $("#dataForm").validate({
        errorClass: "is-invalid", // Bootstrap class for highlighting errors
        rules: {
            task_name: {
                required: true,
                pattern: /^[a-zA-Z0-9\s]*$/,
            },
            startDate: {
                required: true,
                validDate: true
            },
            endDate: {
                required: true,
                validDate: true
            },
            proj_typ_id: {
                required: true,
            },
            proj_id: {
                required: true,
            },
            "team_type[]": {
                required: function(element) {
                // validateCheckboxGroup("team_type[]", "error_team_type",'step5');
                return $(element).val() === null || $(element).val().length === 0;
                },
            },
            "team_ids[]": {
                required: function() {
                    // validateCheckboxGroup("team_ids[]", "error_team_name",'step6');
                    return $("select[name='team_ids[]']").val().length === 0;
                },
            },
            "team_members_ids[]": {
                required: function() {
                    //  validateCheckboxGroup("team_ids[]", "error_team_member",'step7');
                    return $("select[name='team_members_ids[]']").val().length === 0;
                },
            },
        },
        messages: {
            task_name: {
                required: "Task name is required.",
                pattern: "Special characters are not allowed in the Task Name.",
            },
            startDate: {
                required: "Please select a valid start date.",
            },
            endDate: {
                required: "Please select a valid end date.",
            },
            proj_typ_id: {
                required: "Please select a Project Type.",
            },
            proj_id: {
                required: "Please select a Project Name.",
            },
            "team_type[]": {
                required: "Please select at least one Team Type.",
            },
            "team_ids[]": {
                required: "Please Select Team to Assign.",
            },
            "team_members_ids[]": {
                required: "Please Select Team Members.",
            },
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
   // $("#dataForm").valid();
     $("#dataForm").validate().form();

          $('#proj_typ_id').change(function() {
          $('#proj_id').attr('style','pointer-events:visible;');
              var type = $(this).val();

              if(type) {
                  $.ajax({
                      url: '/get_projects/' + type,
                      type: 'GET',
                      success: function(data) {
                          $('#proj_id').empty();
                          $('#proj_id').append('<option value="">Select Project</option>');
                          $.each(data, function(id, name) {
                              $('#proj_id').append('<option value="' + id + '">' + name + '</option>');
                          });
                           
                      }
                  });
              } else {
                  $('#proj_id').empty();
                  $('#proj_id').append('<option value="">Select Project</option>');
              }
          });
          $('#team_type option').on('mousedown', function () {
    lastClickedIndex = this.index;
});

    $('#team_type').on('change', function () {
        const selectedOptions = $('#team_type option:selected');
        if (selectedOptions.length > 2) {
            // Deselect the last selected
             // selectedOptions.first().prop('selected', false);
            $('#team_type option:selected').prop('selected', false);
            //this.options[this.selectedIndex+2].selected = false;
          
        $(this).multiselect('reload');
       $("#error_team_type_msg").show().append("You can only select up to 2 options.");

        setTimeout(function() {
            $("#error_team_type_msg").fadeOut();
        }, 30000);

        
    } else {
        $("#error_team_type_msg").fadeOut();
    }
    });

          $('#team_type').change(function() {
          var type = $(this).val();
                var proj_type = $('#proj_typ_id').val();
                if(type) {
                    $.ajax({
                        url: '/get_teams/' + type,
                        type: 'GET',
                         data: { 
                                team_types: type,
                                proj_type:proj_type,
                              },
                        success: function(data) {
                            $('#team_ids').empty();
                            $('#team_ids').append('<option value="">Select Team</option>');
                            $.each(data, function(id, name) {
                                $('#team_ids').append('<option value="' + id + '">' + name + '</option>');
                            });
                            $('#team_ids').multiselect('reload');
                        }
                    });
                } else {
                    $('#team_ids').empty();
                    $('#team_ids').append('<option value="">Select Team</option>');
                }
            });

      $('#team_ids').change(function() {
          
          var team_ids = $(this).val();

          if(team_ids) {
              $.ajax({
                  url: '/get_teams_members/' + team_ids,
                  type: 'GET',
                  data: { team_ids: team_ids },
                  success: function(data) {
                  // alert(JSON.stringify(data));
                      $('#team_members_ids').empty();
                      $('#team_members_ids').append('<option value="">Select Team Members</option>');
                       $.each(data, function(index, member) {
                                    $('#team_members_ids').append(
                                        `<option value="${member.id}--${member.team_id}" selected>${member.name} - ${member.team_name}</option>`
                                    );
                                });
                       $('#team_members_ids').multiselect('reload');
                       $('#generate_tool').show();
                       $('#assign_task_id').hide(); 
                       $('#saveBtn').prop('disabled', true);
                  }
              });
          } else {
              $('#team_ids').empty();
              $('#team_ids').append('<option value="">Select Team</option>');
          }
      });
      $('#team_members_ids').change(function(){
         $('#generate_tool').show();
         $('#assign_task_id').hide(); 
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
         