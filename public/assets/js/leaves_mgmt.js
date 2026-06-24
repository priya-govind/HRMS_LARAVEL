    $(document).ready(function () {
        $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        const myModal = new bootstrap.Modal(document.getElementById('LeaveModal'), {
            backdrop: 'static',
            keyboard: false
        });
        $('.leave_cl').hide();
        $('.permit_cl').hide();
        $('#FilterForm').on('submit', function(e) {
            e.preventDefault();
            $('#LeaveTable').DataTable().ajax.reload();
        });
        $('#clearFiltersBtn').on('click', function () {
                $('#FilterForm')[0].reset();
                $('#from_dt_search').val('').trigger('change');
                $('#to_dt_search').val('').trigger('change');
                $('#leave_type').val('');
                
                var table = $('#LeaveTable').DataTable();
                table.search('').columns().search('').draw();
                table.ajax.reload(null, true); // true = reset pagination
            });
    $('#dataForm').validate({
        rules: {
            leave_status: {
                required: true
            },
            comments: {
                required: function () {
                    return $('input[name="leave_status"]:checked').val() == "2";
                }
            }
        },
        highlight: function (element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        },
        messages: {
            leave_status: "Please select Approve or Deny.",
            comments: "Comments are required when denying leave."
        },
       submitHandler: function (form) {
         $('input[name="leave_type1"]').prop('disabled', false);
            const formData = $(form).serialize();
            const url = 'change_leave_state';
            const method = 'POST';
            $.ajax({
                url: url,
                type: method,
                data: formData,
                beforeSend: function () {
                    $('#saveBtn').prop('disabled', true).text('Saving...');
                     $('.sender_load').show();
                },
                success: function (response) {
                    $('#success-message1').text(response.message).fadeIn().delay(1000).fadeOut();

                setTimeout(function () {
                    $('#success-message1').hide();
                    myModal.hide();
                    var table = $('#LeaveTable').DataTable();
                   table.ajax.reload(null, false);
                    $('#dataForm').find('input, textarea, select').each(function () {
                        if ($(this).is(':checkbox') || $(this).is(':radio')) {
                            $(this).prop('checked', false);
                        } else {
                            $(this).val('');
                        }
                    });
                }, 3000);
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    $('#error-message1').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
                },
                complete: function () {
                    $('#saveBtn').prop('disabled', false).text('Save');
                    $('.sender_load').hide();
                }
            });
        }
    });
    $('input[name="leave_status"]').on('change', function () {
        if ($(this).val() == "1") {
            $('#comments').val('');
        }
    });
  $('.leave_type').click(function () {
    let leave_typ=$(this).val();
    if(leave_typ=='1'){
        $('.leave_cl').show();
        $('.permit_cl').hide();
         // Add required attributes for leave fields
            $('#from_dt, #to_dt').attr('required', true);
            $('#permission_dt, #from_time, #to_time').removeAttr('required');

    } else {
        $('.leave_cl').hide();
        $('.permit_cl').show();
         // Add required attributes for permission fields
            $('#permission_dt, #from_time, #to_time').attr('required', true);
            $('#from_dt, #to_dt').removeAttr('required');

    }
  });   
  $(document).off('click', '.editButton').on('click', '.editButton', function () {
    myModal.show();
    const id = $(this).data('id');
    var page_name=get_uri_segment(3);
    if(page_name=='leave_approval_requests'){
        title='Change Status of Leave / Permission Request';
    } else {
        title='View Leave / Permission';
    }
    $.get('/view_leave_info/' + id, function (data) {
        $('#dataForm')[0].reset();
        $('#leave_cmt').hide();
         $('#view_comments').html('');
          $('#leave_status').html('');
        $('.approve_permit').hide();
        $('#LeaveModalLabel').text(title);
        $('#emp_name').val(data.emp_name.name);
        $('#recordId').val(data.id);
        $('#emp_id').val(data.emp_id);
        $('input[name="leave_type1"][value="' + data.leave_type + '"]').prop('checked', true);
        if(data.leave_type=='1'){
        $('.leave_cl').show();
        $('.permit_cl').hide();
    } else {
        $('.leave_cl').hide();
        $('.permit_cl').show();
    }
   
        $('#from_dt1').val(data.from_dt);
        $('#to_dt1').val(data.to_dt);
        $('#from_time1').val(data.from_time);
        $('#to_time1').val(data.to_time);
        $('#permission_dt1').val(data.from_dt);
        $('#reason1').val(data.reason);
        $('input[name="leave_type1"][value="' + data.leave_type + '"]').prop('checked', true);
        if(data.leave_status!='0'){
            $('#leave_cmt').show();
            $('#view_comments').html(data.reason_status);
        }
         if (data.leave_status=='1'){
             $('#leave_status').html('Approved');
        }else if (data.leave_status=='2'){
             $('#leave_status').html('Rejected');
        }else{
             $('#leave_status').html('Waiting for Approval');
        } 
      if((data.emp_id != $('#UserId').val()) && (data.leave_status==0 ||  data.leave_status==2)){
        $('.approve_permit').show();
        $('.view_permit').hide();
    } else {
        $('.view_permit').show();
    }
    });
});
$.validator.addMethod("notEmpty", function (value) {
    return $.trim(value).length > 0;
}, "This field cannot be empty.");
$.validator.addMethod("customDate", function(value, element) {
    return /^\d{2}-\d{2}-\d{4}$/.test(value);
}, "Please enter a date in DD-MM-YYYY format");
    $("#LeaveForm").validate({
        errorClass: "is-invalid",
        rules: {
            leave_type: { required: true },
            reason: { required: true, notEmpty: true }
        },
        messages: {
            leave_type: { required: "Please select a leave type." },
            reason: { required: "Please provide a reason." }
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
        submitHandler: function (form) {
            const formData = $(form).serialize();
            const url = 'request_off_day';
            const method = 'POST';
            $.ajax({
                url: url,
                type: method,
                data: formData,
                beforeSend: function () {
                    $('#saveBtn').prop('disabled', true).text('Saving...');
                     $('.sender_load').show();
                },
                success: function (response) {
                    $('#success-message1').text(response.message).fadeIn().delay(1000).fadeOut();

                setTimeout(function () {
                    var table = $('#LeaveTable').DataTable();
                   table.ajax.reload(null, false);
                    $('#success-message1').hide();
                    $('#LeaveForm').find('input, textarea, select').each(function () {
                        if ($(this).is(':checkbox') || $(this).is(':radio')) {
                            $(this).prop('checked', false);
                        } else {
                            $(this).val('');
                        }
                    });
                }, 3000);
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    $('#error-message1').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
                },
                complete: function () {
                    $('#saveBtn').prop('disabled', false).text('Save');
                    $('.sender_load').hide();
                }
            });
        }
    });
    $('#FilterForm').validate({
        rules: {
            from_dt_search: {
                 required: {
                        depends: function () {
                         return $("#leave_type").val() === "";
                        }
                    },
                customDate: {
                    depends: function () {
                        return $("#leave_type").val() === "";
                    }
                }
            },
            to_dt_search: {
                 required: {
                    depends: function () {
                        return $("#leave_type").val() === "";
                    }
                },
                customDate: {
                    depends: function () {
                        return $("#leave_type").val() === "";
                    }
                }
            }
        },
        messages: {
            from_dt_search: "Please enter a valid from date",
            to_dt_search: "Please enter a valid to date"
        },
        errorElement: 'span',
        errorClass: 'text-danger',
        highlight: function (element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function (form) {
            $('#LeaveTable').DataTable().ajax.reload();
        }
    });
    $("#leave_type").on("change", function () {
        $("#FilterForm").validate().element("#from_dt_search");
        $("#FilterForm").validate().element("#to_dt_search");
    });
   $("#from_dt,#to_dt,#permission_dt,#permission_dt_search").datepicker({
    dateFormat: "dd-mm-yy",
     minDate: 0,
    onSelect: function(selectedDate) {
        var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate); // Parse selected date
        var minDate = new Date(dateObj);
        minDate.setDate(minDate.getDate()); // Ensure endDate is at least 1 day ahead
        $("#to_dt").datetimepicker("option", "minDate", minDate);
    }
});
   $("#from_dt_search,#to_dt_search").datepicker({
    dateFormat: "dd-mm-yy",
    onSelect: function(selectedDate) {
        var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate); // Parse selected date
        var minDate = new Date(dateObj);
        minDate.setDate(minDate.getDate() ); // Ensure endDate is at least 1 day ahead
        $("#to_dt_search").datetimepicker("option", "minDate", minDate);
    }
});
$('#from_time').timepicker({
    timeFormat: 'hh:mm TT',
    interval: 15,
    minTime: '09:00 AM',
    maxTime: '05:00 PM',
    dropdown: true,
    scrollbar: true,
    onSelect: function (timeText) {
        updateToTimeRange(timeText);
    },
    onClose: function () {
        const timeText = $(this).val();
        updateToTimeRange(timeText);
    }
});
$('#to_time').timepicker({
    timeFormat: 'hh:mm TT',
    interval: 15,
    minTime: '09:00 AM',
    maxTime: '06:00 PM',
    dropdown: true,
    scrollbar: true
});
      const approveModal = new bootstrap.Modal(document.getElementById('PermissionModal'), {
            backdrop: 'static',
            keyboard: false
        });
$(document).off('click', '.permit_popup').on('click', '.permit_popup', function () {
        $('#PermissionModalLabel').text('Leave Approval Rights');
        const id = $('#UserId').val();
        const module=$('#module_name').val();
            $.get('/retrive_permission/' + id+'/'+module, function (data) {
                if (data && data.permissions) {
                $('.roleCheckbox').each(function () {
                    const empId = parseInt($(this).val());
                    $(this).prop('checked', data.permissions.includes(empId));
                });
            }
            approveModal.show();
    });
});
 $('#assignForm').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission
        const formData = $(this).serialize(); // Serialize all form inputs
        const url = 'assign_module'; // Replace with your actual route
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function () {
                $('#permitBtn').prop('disabled', true).text('Saving...');
            },
            success: function (response) {
            $('#permit_success').text(response.message).fadeIn().delay(1000).fadeOut();
               $('#assignForm')[0].reset();
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON?.message || 'Something went wrong.';
                $('#permit_error').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
            },
            complete: function () {
                setTimeout(function () {
                    $('#permit_success').hide();
                     $('#permitBtn').prop('disabled', false).text('Save');
                    approveModal.hide();
                }, 3000);
               
            }
        });
    });
function updateToTimeRange(timeText) {
    const fromTime = parseTime(timeText);
    if (!fromTime) return;

    const toTimeMin = new Date(fromTime.getTime() + 1 * 60 * 1000); // +1 minute
    let toTimeMax = new Date(fromTime.getTime() + 2 * 60 * 60 * 1000); // +2 hours

    // Define office end time: 6:00 PM
    const officeEndTime = new Date(fromTime);
    officeEndTime.setHours(18, 0, 0); // 6:00 PM

    // Cap toTimeMax at 6:00 PM
    if (toTimeMax > officeEndTime) {
        toTimeMax = officeEndTime;
    }

    $('#to_time').timepicker('destroy');
    $('#to_time').val('').timepicker({
        timeFormat: 'hh:mm TT',
        interval: 15,
        minTime: formatTime(toTimeMin),
        maxTime: formatTime(toTimeMax),
        dropdown: true,
        scrollbar: true
    });

    setTimeout(function () {
        $('#to_time').focus();
    }, 150);
}
function parseTime(timeStr) {
    const [time, modifier] = timeStr.split(' ');
    const [hours, minutes] = time.split(':');
    let hrs = parseInt(hours, 10);
    if (modifier === 'PM' && hrs < 12) hrs += 12;
    if (modifier === 'AM' && hrs === 12) hrs = 0;
    const date = new Date();
    date.setHours(hrs);
    date.setMinutes(parseInt(minutes, 10));
    date.setSeconds(0);
    return date;
}
function formatTime(date) {
    let hrs = date.getHours();
    const mins = date.getMinutes();
    const modifier = hrs >= 12 ? 'PM' : 'AM';
    hrs = hrs % 12 || 12;
    const paddedMins = mins < 10 ? '0' + mins : mins;
    return `${hrs}:${paddedMins} ${modifier}`;
}
});