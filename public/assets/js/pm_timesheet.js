$(document).ready(function () { 
      $('#create_dt').on('change', function() {
            let selectedDate = $(this).val();
            $.get('/timesheets/last-entry', { date: selectedDate }, function(data) {
               // alert(JSON.stringify(data));
                if (data.next_from && data.next_to) {
                    /**All entries except first entry */
                      if(data.sys_prob==1){
                       $('#from_time').append('<option value="'+data.entry_time_chkin+'">'+data.entry_time_chkin+'</option>');
                       $('#from_time').val(data.entry_time_chkin).prop('disabled', true);
                       $('#to_time').val(data.next_to).prop('disabled', true);
                       $('#hidden_from').val(data.entry_time_chkin);
                       $('#hidden_to').val(data.next_to);
                     } else if (data.next_from!="9:00 AM"){
                        $('#from_time').append('<option value="'+data.next_from+'">'+data.next_from+'</option>');
                        $('#from_time').val(data.next_from).prop('disabled', true);
                        $('#to_time').val(data.next_to).prop('disabled', true);
                        $('#hidden_from').val(data.next_from);
                        $('#hidden_to').val(data.next_to);
                     } 
                     /**First entry is equal to 9 AM */
                     else if (data.next_from==data.chk_in_time){
                         $('#from_time').append('<option value="'+data.next_from+'">'+data.next_from+'</option>');
                        $('#from_time').val(data.next_from).prop('disabled', true);
                        $('#to_time').val(data.next_to).prop('disabled', true);
                        $('#hidden_from').val(data.next_from);
                        $('#hidden_to').val(data.next_to);
                     } else{
                        /**fIRST ENTRY BASED ON CHKIN TIME */
                       $('#from_time').append('<option value="'+data.chk_in_time+'">'+data.chk_in_time+'</option>');
                       $('#from_time').val(data.chk_in_time).prop('disabled', true);
                       $('#to_time').val(data.next_to).prop('disabled', true);
                       $('#hidden_from').val(data.chk_in_time);
                       $('#hidden_to').val(data.next_to);
                     }
                } else if (data.last_to) {
                    $('#from_time').val(data.last_from).prop('disabled', true);
                    const toTimeDropdown = $('#to_time');
                        if (toTimeDropdown.find(`option[value="${data.last_to}"]`).length === 0) {
                            toTimeDropdown.prepend(
                                `<option value="${data.last_to}">${data.last_to}</option>`
                            );
                        }
                        $('#to_time').val(data.last_to).prop('disabled', true);
                   // $('#to_time').val(data.last_to);
                    $('#hidden_from').val(data.last_from);
                    $('#hidden_to').val(data.last_to);
                } else {
                    $('#from_time').val('09:00 AM').prop('disabled', false);
                    $('#to_time').val('');
                    $('#hidden_from').val('09:00 AM');
                    $('#hidden_to').val();
                }
            });
    const addModal = new bootstrap.Modal(document.getElementById('timesheetModal'), {
        backdrop: 'static',
        keyboard: false
    });
    addModal.show();
     });
$('#addTimesheetBtn').click(function() {
    $('#timesheetModalLabel').text('Add Timesheet Entry');
    $('#time_mode').val('add');
    $('button[type="submit"].btn-success').show();
    $('#customTaskWrapper').hide();
    $('#customProjectWrapper').hide();
    $('#customModuleWrapper').hide();
    $('#timesheetForm')[0].reset();
      // Clear time dropdowns initially
       $('#entry_id').val('');
    window.isEditing = false;
    $('#from_time').empty().val('');
    $.get('/timesheets/dates', function(response) {
        $('#create_dt').empty();
        // Always add current date
        $('#create_dt').append('<option value="'+response.current_date+'">'+response.current_date+'</option>');
        // Add previous date if available
        if (response.previous_date) {
            $('#create_dt').append('<option value="'+response.previous_date+'">'+response.previous_date+'</option>');
        }
        // Default select current date
        $('#create_dt').val(response.current_date).trigger('change');
    });

    $('#timesheetForm').find('input, select, textarea').each(function() {
        if ($(this).is('input[type="text"], textarea')) {
            $(this).prop('readonly', false);
        } else {
            $(this).prop('disabled', false);
        }
    });

   

    // Handle hidden_from / hidden_to values
    const fromVal = $('#hidden_from').val();
    const toVal   = $('#hidden_to').val();

    if (fromVal !== '' && toVal !== '') {
        const fromDropdown = $('#from_time');
        const toDropdown   = $('#to_time');

        // Ensure from_time option exists
        if (fromDropdown.find(`option[value="${fromVal}"]`).length === 0) {
            fromDropdown.append(`<option value="${fromVal}">${fromVal}</option>`);
        }

        // Ensure to_time option exists
        if (toDropdown.find(`option[value="${toVal}"]`).length === 0) {
            toDropdown.append(`<option value="${toVal}">${toVal}</option>`);
        }

        // Set values
        fromDropdown.val(fromVal).trigger('change');
        toDropdown.val(toVal);
    }
});

  
              const toTimes = [
                                "10:00 AM","10:45 AM","12:00 PM","1:00 PM","1:30 PM","2:00 PM",
                                "3:00 PM","4:00 PM","5:00 PM","6:00 PM"
                            ];
        function timeToMinutes(t) {
                // Convert "HH:MM AM/PM" to minutes
            if (!t || typeof t !== 'string') {
                console.warn("timeToMinutes called with invalid value:", t);
                return 0; // safe fallback
            }
            let parts = t.match(/(\d{1,2}):(\d{2})\s?(AM|PM)/i);
            if (!parts) {
                console.warn("Invalid time format:", t);
                return 0;
            }
            let hours = parseInt(parts[1], 10);
            let minutes = parseInt(parts[2], 10);
                let ampm = parts[3].toUpperCase();
                if (ampm === "PM" && hours < 12) hours += 12;
                if (ampm === "AM" && hours === 12) hours = 0;
                return hours * 60 + minutes;
        }
    $('#from_time').change(function() {
        let fromVal = $(this).val();
        let fromMinutes = timeToMinutes(fromVal);
        $('#to_time').empty();
        $.each(toTimes, function(i, t) {
            if (timeToMinutes(t) > fromMinutes) {
                $('#to_time').append('<option value="'+t+'">'+t+'</option>');
            }
        });
    });
    $('#from_time').trigger('change');
        $('#from_time, #to_time').change(function() {
        let from = $('#from_time').val();
        let to   = $('#to_time').val();
        if(from && to ) {
            var checkUrl = '/timesheets/check-entry'
                    + '?date=' + encodeURIComponent($('#create_dt').val())
                    + '&from_time=' + encodeURIComponent(from)
                    + '&to_time=' + encodeURIComponent(to);
            $.get(checkUrl, function(data) {  
                if(data.exists  && window.isEditing) {
                    $('textarea[name="comments"]').val(data.entry.comments);
                    $('#project_id').val(data.entry.project_id);
                    $('#module_id').val(data.entry.module_id);
                    $('#task_id').val(data.entry.task_id).trigger('change');
                    if(data.entry.task_id === 'other') {
                        $('#custom_task').val(data.entry.custom_task);
                    }
                } 
                /** This part effect when view task option */
                else {
                    // Clear form if no entry
                    if($('#entry_id').val()=='' || (!window.isEditing)){
                        $('#timesheetForm')[0].reset();
                    }
                }
            });
        }
    });
    $('#project_id').on('change', function() {
        let proj_id = $(this).val();
        if(!proj_id) return;
            if ($(this).val() === 'other') {
                                $('#customProjectWrapper').show();
                                $('#customModuleWrapper').show();
                                $('#customTaskWrapper').show();
                                $('#module_id').empty().append('<option value="other">Other (Create new Module)</option>');
                                $('#task_id').empty().append('<option value="other">Other (Create new Task)</option>');
            } else {
                    $.get('/get_project_modules/' + proj_id, function(modules) {
                        $('#module_id').empty().append('<option value="">Select Module</option>');
                         if (!$.isEmptyObject(modules) || !modules.length === 0) {
                                $.each(modules, function(id, name) {
                                    $('#module_id').append('<option value="'+id+'">'+name+'</option>');
                                });
                                $('#module_id').append('<option value="other">Other (Create new Module)</option>');
                                $('#customProjectWrapper').hide();
                                $('#customModuleWrapper').hide();
                                $('#customTaskWrapper').hide();
                            } else {
                                $('#module_id').empty().append('<option value="">Select Module</option>');
                                $('#module_id').append('<option value="other">Other (Create new Module)</option>');
                                $('#module_id').val('other');
                                $('#task_id').val('other');
                                $('#customModuleWrapper').show();
                                $('#customTaskWrapper').show();
                            }
                    });
                     // Preselect if editing
                        if(window.editModuleId) {
                            $('#module_id').val(window.editModuleId).trigger('change');
                            window.editModuleId = null;
                        }
            }
    });
    $('#module_id').on('change', function() {
          let proj_id = $('#project_id').val();
          let module_id = $(this).val();
          if(!module_id) return;
                 if ($(this).val() === 'other') {
                        $('#customModuleWrapper').show();
                         $('#customTaskWrapper').show();
                        $('#task_id').empty().append('<option value="other">Other (Create new task)</option>');
                } else {
                    $.get('/get_mapped_tasks/'+proj_id+'/'+module_id, function(tasks) {
                        $('#task_id').empty().append('<option value="">Select Task</option>');
                        if (!$.isEmptyObject(tasks) || !tasks.length === 0) {
                            $.each(tasks, function(id, name) {
                                $('#task_id').append('<option value="'+id+'">'+name+'</option>');
                            });
                            $('#task_id').append('<option value="other">Other (Create new task)</option>');
                        } else {
                            $('#task_id').append('<option value="other">Other (Create new task)</option>');
                             $('#task_id').val('other');
                             $('#customTaskWrapper').show();
                        }
                           // $('#custom_project').val('');
                        // Preselect if editing
                        if(window.editTaskId) {
                            $('#task_id').val(window.editTaskId).trigger('change');
                            window.editTaskId = null;
                        }
                    });
                    $('#customModuleWrapper').hide();
             }
      });
        $('#task_id').change(function() {
            if ($(this).val() === 'other') {
                // Show another modal for custom task
                // $('#otherTaskModal').modal('show');
                $('#customTaskWrapper').show();
            } else {
                    $('#customTaskWrapper').hide();
                }
        });

 
 $("#timesheetForm").validate({
        errorClass: "is-invalid", // Bootstrap highlight
        rules: {
            from_time: {
                required: function() {
                    // Only required in Add mode (no entry_id)
                    return $('#entry_id').val() === null || $('#entry_id').val() === "";
                }
            },
            to_time: { required: true },
            project_id: { required: function() {
                    // Only required in Add mode (no entry_id)
                    return $('#custom_project').val() === null || $('#custom_project').val() === "";
                } },
            custom_project:{ 
                        required:function(){
                            return $('#project_id').val()=="other";
                        }
            },
            module_id: { required: function() {
                    // Only required in Add mode (no entry_id)
                    return $('#custom_module').val() === null || $('#custom_module').val() === "";
                } },
            custom_module:{ 
                        required:function(){
                            return $('#module_id').val()=="other";
                        }
            },
            task_id: { required: function() {
                    // Only required in Add mode (no entry_id)
                    return $('#custom_task').val() === null || $('#custom_task').val() === "";
                } },
            custom_task:{ 
                        required:function(){
                            return $('#task_id').val()=="other";
                        }
            },
            comments: { required: true }
        },
        messages: {
            from_time: { required: "Select the Start Time" },
            to_time:   { required: "Select the End Time" },
            project_id:{ required: "Select the Project" },
            custom_project:{ required: "Enter Custom Project Name" },
            module_id: { required: "Select the Module" },
            custom_module:{ required: "Enter Custom Module Name" },
            task_id:   { required: "Select the Task" },
            custom_task:{ required: "Enter Custom Task Name" },
            comments:  { required: "Enter the Comments" }
        },
        highlight: function(element, errorClass) {
            $(element).addClass(errorClass);
        },
        unhighlight: function(element, errorClass) {
            $(element).removeClass(errorClass);
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.parent());
        },
        submitHandler: function(form) {
            // Ensure disabled selects are included
            $('#from_time').prop('disabled', false);
            $('#to_time').prop('disabled', false);
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: $(form).serialize(),
                success: function(response) {
                    if (response.success === false) {
                        $('#error-message1').text('Error: ' + response.message).show();
                        return;
                    }
                    $('#success-message1').text(response.message).show();
                    setTimeout(function () {
                        $('#success-message1').hide();
                        var timesheetModal = bootstrap.Modal.getInstance(document.getElementById('timesheetModal'));
                        if (timesheetModal) timesheetModal.hide();
                        $('#categoryTable').DataTable().ajax.reload(null, false);
                       $('#timesheetForm').trigger('reset');
                        $('#custom_project').val('');
                         $('#custom_module').val('');
                          $('#custom_task').val('');
                        if (response.lock === true ) {
                            $('#addTimesheetBtn').prop('disabled', true);
                        }
                        if (!window.isEditing && response.next_from && response.next_to) {
                            $('#from_time').val(response.next_from).trigger('change');
                            $('#to_time').val(response.next_to).trigger('change');
                            $('#hidden_to').val(response.next_to);
                             $('#hidden_from').val(response.next_from);
                        }
                        //  else {
                        //     location.reload();
                        // }
                    }, 2000);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            let input = $('[name="'+field+'"]');
                            input.addClass('is-invalid');
                            input.after('<span class="text-danger error-message">'+messages[0]+'</span>');
                        });
                    }
                    if (xhr.status === 500) {
                        alert('Something went wrong while saving.');
                    }
                }
            });
        }
    });
$(document).on("click", ".edit-timesheet", function() {
    const id = $(this).data('id');
    $('#time_mode').val('edit');

    $.get('/timesheet/edit_dtls/' + id, function(res) {
        const entry = res.entry;
        
        // Basic resets
        $('#entry_id').val(entry.id);
        $('#comments').val(entry.comments);
        window.isEditing = true;

        // Date & Times
        $('#create_dt').empty().append(`<option value="${entry.create_date}" selected>${entry.create_date}</option>`);
        handleTimeDropdown('#from_time', entry.from_time);
        handleTimeDropdown('#to_time', entry.to_time);

        // Project Selection
        if (entry.custom_project) {
            $('#project_id').val('other');
            $('#customProjectWrapper').show();
            $('#custom_project').val(entry.custom_project);
        } else {
            $('#customProjectWrapper').hide();
            // Force selection by attribute
            $('#project_id').val(entry.project_id).find(`option[value="${entry.project_id}"]`).prop('selected', true);

            // Load Modules
            $.get('/get_project_modules/' + entry.project_id, function(modules) {
                const $mod = $('#module_id').empty().append('<option value="">Select Module</option>');
                
                // Handle both array [obj, obj] and object {id: name}
                $.each(modules, function(key, value) {
                    let mId = (typeof value === 'object') ? value.id : key;
                    let mName = (typeof value === 'object') ? value.name : value;
                    $mod.append(`<option value="${mId}">${mName}</option>`);
                });
                $mod.append('<option value="other">Other</option>');

                if (entry.custom_module) {
                    $mod.val('other');
                    $('#customModuleWrapper').show();
                    $('#custom_module').val(entry.custom_module);
                } else {
                    $('#customModuleWrapper').hide();
                    // THE FIX: Set value AND prop
                    $mod.val(entry.module_id).find(`option[value="${entry.module_id}"]`).prop('selected', true);

                    // Load Tasks
                    $.get('/get_mapped_tasks/' + entry.project_id + '/' + entry.module_id, function(tasks) {
                        const $tsk = $('#task_id').empty().append('<option value="">Select Task</option>');
                        $.each(tasks, function(key, value) {
                            let tId = (typeof value === 'object') ? value.id : key;
                            let tName = (typeof value === 'object') ? value.name : value;
                            $tsk.append(`<option value="${tId}">${tName}</option>`);
                        });
                        $tsk.append('<option value="other">Other</option>');

                        if (entry.custom_task) {
                            $tsk.val('other');
                            $('#customTaskWrapper').show();
                            $('#custom_task').val(entry.custom_task);
                        } else {
                            $('#customTaskWrapper').hide();
                            $tsk.val(entry.task_id).find(`option[value="${entry.task_id}"]`).prop('selected', true);
                        }
                    });
                }
            });
        }

        // Show the modal after some gap to ensure all values are loaded
        setTimeout(function() {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('timesheetModal')).show();
        }, 600);
    });
});

function handleTimeDropdown(selector, value) {
    const $d = $(selector);
    if ($d.find(`option[value="${value}"]`).length === 0) {
        $d.append(`<option value="${value}">${value}</option>`);
    }
    $d.val(value).prop('disabled', true);
}
        $(document).on("click", ".view-timesheet", function() {
        const id = $(this).data('id');
         $('#time_mode').val('view');
        $.get('/timesheet/edit_dtls/' + id, function(res) {
        $('#timesheetModalLabel').text('View Timesheet Entry');
            const entry = res.entry;
            $('#entry_id').val(entry.id);
            window.editFromTime = entry.from_time;
            window.editToTime   = entry.to_time;
            window.isEditing  = true;
             $('button[type="submit"].btn-success').hide();
                $('#timesheetForm').find('input, select, textarea').each(function() {
                        // For inputs/textareas → use readonly
                        if ($(this).is('input[type="text"], textarea')) {
                            $(this).prop('readonly', true);
                        } else {
                            // For selects, checkboxes, radios → disable
                            $(this).prop('disabled', true);
                        }
                    });
            $('#create_dt').empty();
            $('#create_dt').append('<option value="'+entry.create_date+'">'+entry.create_date+'</option>');
             $('#create_dt').val(entry.create_dt);
            $('#from_time').val(entry.from_time);
            
            if (entry.from_time) {
                $('#from_time').val(entry.from_time).trigger('change');
            }
            if (entry.to_time) {
                $('#to_time').val(entry.to_time);
            }
                $('#from_time').prop('disabled', true);
                $('#to_time').prop('disabled', true);
                // Save desired IDs globally
                window.editModuleId = entry.module_id;
                window.editTaskId   = entry.task_id;
            // Set project first → triggers module load
            if(entry.custom_project) {
                    $('#customProjectWrapper').show();
                    $('#custom_project').val(entry.custom_project);
                    $('#project_id').val("other");
                } else {
                    $('#customProjectWrapper').hide();
                    $('#custom_project').val('');
                    $('#project_id').val(entry.project_id).trigger('change');
                }
                if(entry.custom_module) {
                    $('#customModuleWrapper').show();
                    $('#custom_module').val(entry.custom_module);
                    $('#module_id').val("other");
                } else {
                    $('#customModuleWrapper').hide();
                    $('#custom_module').val('');
                }
                if(entry.custom_task) {
                    $('#customTaskWrapper').show();
                    $('#custom_task').val(entry.custom_task);
                    $('#task_id').val("other");
                } else {
                    $('#customTaskWrapper').hide();
                    $('#custom_task').val('');
                }
                // Fill other fields
                $('#comments').val(entry.comments);
                $('#create_dt').val(entry.create_date);

            new bootstrap.Modal(document.getElementById('timesheetModal')).show();
        });
    });
  });