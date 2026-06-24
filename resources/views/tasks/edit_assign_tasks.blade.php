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
          <div class="col-md-10 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                  <nav class="p-1">
                  <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="{{ route('tasks.manage_tasks') }}">Tasks</a></li>
                      <li class="breadcrumb-item active">Edit Assigned Task</li>
                  </ol>
              </nav>
                    <h4 class="card-title">Assign Tasks</h4>
                    <div id="success-message1" class="alert alert-success"  role="alert"  style="display: none;"></div>
                    <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                    <form id="dataForm" method="post" action="{{ route('update_task', $tasks['id']) }}">
                          @csrf
                          @method('PUT')
                        <!--  -->

                      <input type="hidden" id="recordId" name="recordId" value="{{ $tasks['id'] }}">
                      <div class="form-group">
                        <label for="exampleInputUsername1">Task Name</label>
                        <div class="col-6">
                        <input type="text" class="form-control" id="task_name" placeholder="Task Name" name="task_name" value="{{ $tasks['task_name'] }}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">Start Date</label>
                        <div class="col-6">
                        <input type="text" class="form-control" id="startDate" name="startDate" placeholder="Start Date" readonly  value="{{ $datas['startDate'] }}">
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">End Date</label>
                        <div class="col-6">
                         <input type="text" class="form-control"  id="endDate" name="endDate" placeholder="End Date" readonly  value="{{ $datas['endDate'] }}">   
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">Select Project Type</label>
                        <div class="dropdown col-6">
                        <select name="proj_typ_id" id="proj_typ_id" class="form-control frm_ctrl_select">
                          <option value="">Select</option>
                          @foreach($project_type as  $proj_typ)
     
                              <option value="{{ $proj_typ->id }}" @if($proj_typ->id== $tasks['proj_typ_id'])  selected  @endif >{{ $proj_typ->proj_typ_name}}</option>
                          @endforeach
                      </select>
                     
                       </div>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputUsername1">Select Project Name</label>
                        <div class="dropdown col-6">
                        <select name="proj_id" id="proj_id" class="form-control frm_ctrl_select">
                          <option value="">Select</option>
                          @foreach($projects as $proj)
                              <option value="{{ $proj->id }}" @if($proj->id== $tasks['proj_id'])  selected  @endif >{{ $proj->proj_name }}</option>
                          @endforeach
                      </select>
                     
                       </div>
                      </div>
                      <div class="form-group">
                          <label for="exampleInputUsername1">Select Team Type</label>
                          <div class="dropdown col-6">
                              <select name="team_type[]" id="team_type" class="form-control frm_ctrl_select" style="height: 8% !important;" multiple>
                                  <option  value="">Select</option>
                                  @foreach($team_type as $tem_typ)
                                        <option value="{{ $tem_typ->id }}" @if(in_array($tem_typ->id, $datas['selected_team_type'])) selected @endif> {{ $tem_typ->team_typ_name }} </option>
                                    @endforeach
                              </select>
                              
                          </div>
                          <div id="error_team_type" class="error-text" style="display: none;"></div>
                      </div>
                      <div class="form-group">
                          <label for="exampleInputUsername1">Select Team Name</label>
                          <div class="dropdown col-6">
                          <select name="team_ids[]" id="team_ids" class="form-control frm_ctrl_select" style="height: 8% !important;" multiple>
                          <option  value="">Select</option>
                                  @foreach($datas['all_teams'] as $index => $value)
                                        <option value="{{ $index }}" @if(in_array($index, $datas['selected_teams'])) selected @endif> {{ $value }} </option>
                                    @endforeach
                          </select>
                          </div>
                           <div id="error_team_name" class="error-text" style="display: none;"></div>
                      </div>
                      <div class="form-group">
                        <div class="row">
                            <div class="col-md-7">
                            <label for="exampleInputEmail1">Select Team Members</label>
                            <div class="dropdown">
                            <select name="team_members_ids[]" id="team_members_ids" class="form-control frm_ctrl_select" style="height: 93% !important;width: 88% !important;" multiple>
                            <option  value="">Select</option>
                            @foreach($datas['team_members'] as $member)
                                    <option value="{{ $member['id'].'--'.$member['team_id'] }}" @if(in_array($member['id'], $datas['selected_employees'])) selected @endif> 
                                        {{ $member['name']}} - {{ $member['team_name'] }} </option>
                            @endforeach
                        </select>
                         </div>
                        <div id="error_team_member" class="error-text" style="display: none;"></div>
                                </div>
                         <div class="col-md-4 mt-4" id="generate_tool" style="display:none;">
                        <button name="generate_task" id="generate_task" class="btn btn-danger blinking" type="button">Generate Task for Each Member</button>
                    </div>       
                  </div>
                      </div>
                     <div class="form-group" id="assign_task_id">
                            <div class="row col-md-10">
                                Assign Tasks Individually
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Member</th>
                                            <th>Task</th>
                                            <th>Comments</th>
                                            <th>Action</th>
                                        </tr> </thead>
                                        <tbody id="memberTaskRows">
                                        @foreach ($datas['assignedMembersWithTeam'] as $member) 
                                        
                                        <tr id='emp_{{ $member['pivot_id'] }}'> 
                                            <td><input type="hidden" name="assign_mem_id[]" value="{{ $member['employee_id'] }}">
                                            <input type="hidden" name="assign_team_id[{{$member['employee_id']}}]" value="{{$member['team_id']}}">{{  $member['name'].'-'.$member['team_name'] }}</td>
                                            <td><input type="text"  name="assign_mem_task[{{ $member['employee_id'] }}]" value="{{ $member['task_info'] }}"
                                            @if(!in_array($member['team_id'],session('team_id')))
                                                class="form-control disabledsingle-field" readonly
                                                @else
                                                 class="form-control" 
                                            @endif
                                            ></td>
                                            <td> @if($member['comments']!='') {{ $member['comments'] }} @else No Comments @endif</td>
                                            <td>
                                                @if(in_array($member['team_id'],session('team_id')))
                                                <button type="button" class=" btn btn-danger btn-sm delete-btn_task" data-id="{{ $member['pivot_id']}}" data-emp="{{ $member['employee_id'] }}">
                                                 <i class="fa fa-trash-o"></i> 
                                                </button> 
                                                @endif 
                                             </td>
                                        </tr>
                                        
                                        
                                        @endforeach
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

  <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this item?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
       $("#dataForm").validate();
       $('#proj_typ_id').addClass('disabledsingle-field');
       $('#proj_id').addClass('disabledsingle-field');
       $('#team_type').addClass('disabled-field');
       $('#team_ids').addClass('disabled-field');
       //$('#team_members_ids').addClass('disabled-field');
        //$('#team_members_ids').attr('style','pointer-events:none;').css('background-color:#ccc;height: 8% !important;');
       
   $("#startDate").datetimepicker({
    dateFormat: "dd-mm-yy",
    timeFormat: "HH:mm:ss",
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
        $("#error_team_type").html("<span style='color:red;'>Please select at least one option.</span>").fadeIn();
       // console.log("Span is empty.");
    } else {
        //console.log("Span contains: " + spanText);
          $("#error_team_type").fadeOut();
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
    $('#dataForm').submit(function(event) {

        var startDate = parseDate($("#startDate").val());
        var endDate = parseDate($("#endDate").val());

        if (startDate > endDate) {
           $('#error-message1').text("Start date cannot be greater than end date.").fadeIn().delay(5000).fadeOut();
             $('#saveBtn').prop('disabled', false).text('Save');
            event.preventDefault(); // Stop form submission
        }
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
                      },
                      error: function(xhr) {
                        console.error("Error fetching projects:", xhr);
                        $('#proj_id').empty().append('<option value="">Failed to load projects</option>');
                    }
                  });
              } else {
                  $('#proj_id').empty();
                  $('#proj_id').append('<option value="">Select Project</option>');
              }
          });
          $('#proj_id').change(function() {
            $('#team_type').attr('style','pointer-events:visible;');
          });
$('#team_members_ids').change(function(){
  $('#assign_task_id').hide(); 
  $('#generate_tool').show(); 
    $('#saveBtn').prop('disabled', true).text('Save');
   
});
          $('#team_type').change(function() {
            $('#team_ids').attr('style','pointer-events:visible;');
             
                var type = $(this).val();
                var proj_type = $('#proj_typ_id').val();
               
                if(type) {
                    //alert(type+'--'+proj_type);
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
                $('#team_members_ids').attr('style','pointer-events:visible;');
                
                var team_ids = $(this).val();
                if(team_ids) {
                    $.ajax({
                        url: '/get_teams_members/' + team_ids,
                        type: 'GET',
                        data: { team_ids: team_ids },
                            success: function(data) {
                               $('#team_members_ids').empty();
                                $('#team_members_ids').append('<option value="">Select Team Members</option>');
                               $.each(data, function(index, member) {
                                    $('#team_members_ids').append(
                                        `<option value="${member.id}" selected>${member.name} - ${member.team_name}</option>`
                                    );
                                });

                                $('#team_members_ids').multiselect('reload');
                                $('#generate_tool').show();
                                $('#assign_task_id').hide();  
                            }
                    });
                } else {
                    $('#team_ids').empty();
                    $('#team_ids').append('<option value="">Select Team</option>');
                }
            });

            function validateSelection(element, errorId) {
                var selectedValues = $(element).val() || [];

                if (!selectedValues || selectedValues.length === 0) {
                    $("#" + errorId).html("Please select at least one option.").fadeIn();
                } else {
                    $("#" + errorId).fadeOut();
                }
            }

    $(document).on('click', '.delete-btn_task', function() {

            const itemId = $(this).data('id'); 
            const emp_id=$(this).data('emp'); 
            const url = '/tasks/remove_task_employee';
            const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                            backdrop: 'static',
                            keyboard: false
                        });
                        myModal.show();
               // Add click listener for the Delete button inside the modal
               $('#confirmDelete').off('click').on('click', function () {
                callajax(url, 'DELETE', {
                                            _token: '{{ csrf_token() }}',
                                            itemId: itemId,
                                            }
                            ); 
                    $('#emp_'+itemId).remove();
                    $("#team_members_ids option").filter(function() {
                        return $(this).val().startsWith(emp_id);
                    }).prop("selected", false);
                myModal.hide();  
                   // location.reload();
                });

            
    });
     $('#generate_task').click(function(){
        $("#memberTaskRows").empty(); // Clear previous entries
            
 const selectedValues = $('#team_members_ids').val();
 let processedMembers = selectedValues.map(member => String(member).split("--")[0]);
 let processedTeams = selectedValues.map(member => String(member).split("--")[1]);

    const taskId=$('#recordId').val();
    const url='/tasks/task_employee_update';
            callajax(url, 'PUT', {
                _token: '{{ csrf_token() }}',
                taskId: taskId,
                member_ids: processedMembers,
                team_ids:processedTeams,
            });

                    $.ajax({
                      url: '/tasks/load_members_assigned/' + taskId,
                      type: 'GET',
                      success: function(response) {
                        $("#memberTaskRows").append(response.data);   
                      }
                  });                   
                   $('#assign_task_id').show(); 
                   $('#generate_tool').hide(); 
                   $('#saveBtn').prop('disabled', false).text('Save'); 
                 
    
    });
    // Example delete function
function callajax(url,method,data) {
     
    $.ajax({
                url: url,
                type: method,
                data: data,
                success: function (response) {
                   // alert(JSON.stringify(response));
                    const messageContainer = $('#success-message1'); // Select the container for the message
                    messageContainer.text(response.message).show(); // Update and display the message
            // Hide the message after 5 seconds
                setTimeout(function () {
                    messageContainer.hide();
                    //location.reload();
                }, 2000);
      },
      error: function (xhr) {
        const messageContainer = $('#error-message1'); // Select the container for the error message
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            messageContainer.text('Error: ' + errorMessage).show(); // Update and display the error message
            // Hide the error message after 5 seconds
            setTimeout(function () {
                messageContainer.hide();
            }, 2000);

      }
            });
}
      });
</script>
          @endsection
         