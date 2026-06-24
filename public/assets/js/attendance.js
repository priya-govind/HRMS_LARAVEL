document.addEventListener('DOMContentLoaded', function () {
    // Irregular Checkout Modal
    const modalCheckout = document.getElementById('IrregularChkoutModal');
    if (modalCheckout) {
        const checkoutModal = new bootstrap.Modal(modalCheckout, {
            backdrop: 'static',
            keyboard: false
        });
        checkoutModal.show();
    }

    // Status Modal
    const modalStatus = document.getElementById('statusModal');
    if (modalStatus) {
        const statusModal = new bootstrap.Modal(modalStatus, {
            backdrop: 'static',
            keyboard: false
        });
        statusModal.show();
    }
});
/***modalpop up checkin time */
$('#chkinDate').timepicker({
        timeFormat: 'hh:mm TT',  // Customize time format as needed
        interval: 30,            // 30-minute intervals
        minTime: '12:00am',
        maxTime: '11:30pm',
        startTime: '09:00am',
        dynamic: false,
        dropdown: true,
        scrollbar: true
      });

    function getNextTime(timeStr) {
        var time = new Date('1970-01-01 ' + timeStr);
        time.setMinutes(time.getMinutes() + 15);
        var hours = time.getHours();
        var minutes = time.getMinutes();
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        return hours + ':' + minutes + ' ' + ampm;
    }
var lastCheckin = $('#last_checkin_time').val();
var minTime = getNextTime(lastCheckin);

function getCheckoutLimit(workedPeriod, lastCheckin) {
    const cutoff = "01:30 PM"; // default half-day cutoff
    const endOfDay = "06:00 PM";

    // Convert lastCheckin and 1:00 PM to Date objects
    var checkinDate = new Date('1970-01-01 ' + lastCheckin);
    var onePM = new Date('1970-01-01 01:00 PM');

    // If check-in is after 1:00 PM → allow up to 6 PM
    if (checkinDate >= onePM) {
        return endOfDay;
    }

    // Half Day (morning) → cutoff at 1:30 PM
    if (workedPeriod === "1") {
        return cutoff;
    }

    // Full Day → always ends at 6:00 PM
    return endOfDay;
}


function initCheckoutTimepicker(maxTime) {

    if ($('#chkoutTime').hasClass('hasDatepicker')) {
        $('#chkoutTime').timepicker('destroy'); // ✅ IMPORTANT
    }

    $('#chkoutTime').timepicker({
        timeFormat: 'hh:mm TT',
        interval: 15,
        minTime: minTime,
        maxTime: maxTime,
        dropdown: true,
        scrollbar: true
    });

    $('#chkoutTime').val('');
}

// Initial load → Full Day
initCheckoutTimepicker('06:00 PM');


// Update maxTime dynamically when half/full day is selected
$('#worked_period').on('change', function () {
    var workedPeriod = $(this).val();
    var limit = getCheckoutLimit(workedPeriod, lastCheckin);

    // 🔥 Destroy & reinitialize
    initCheckoutTimepicker(limit);
});

$('#sys_prob').on('change', function() {
  if ($(this).is(':checked')) {
    $('#reason_dv').show();
  } else {
    $('#reason_dv').hide();
  }
});

$('#chkinDate').timepicker('setTime', new Date());
/**  Show/hide comments based on working mode */
    $('#working_mode').on('change', function () {
       const val = $(this).val();
       const time_vl = shouldRequireComments($('#chkinDate').val());
      
       $('#reason_dv').toggle(val === '3' || val === '4' || time_vl);
    });
/**CheckIn Submission */
    // jQuery Validation and AJAX
    $('#statusForm').validate({
      rules: {
        working_mode: { required: true },
        comments: {
          required: function () {
            const timeStr = $('#chkinDate').val();
            const isLate = shouldRequireComments(timeStr);
            const isMode3 = ($('#working_mode').val() === '3' || $('#working_mode').val() === '4');
            const hasSysProb = $('#sys_prob').is(':checked');
             return (isLate || isMode3)|| hasSysProb;

          }
        }
      },
      messages: {
        working_mode: "Select Working Mode.",
        comments: "Enter the Comments."
      },
      errorClass: "is-invalid",
      errorElement: "label",
      errorPlacement: function (error, element) {
        element.after(error);
      },
  submitHandler: function (form) {
    const sysProb = $('#sys_prob').is(':checked');
    const chkinDate = $('#chkinDate').val();

      if (sysProb) {
        const enteredTime = new Date("1970/01/01 " + chkinDate);
        const nowTime = new Date();

        // If user left check-in as current time, warn them
        if (Math.abs(enteredTime.getHours() - nowTime.getHours()) < 1 &&
            Math.abs(enteredTime.getMinutes() - nowTime.getMinutes()) < 10) {
              $('#error-message').html("⚠️ You marked a system problem, but your check-in time looks like the current time. Please adjust it to your actual arrival time (e.g., 9:30 AM).").show()
          return false; // stop submission
        }
      }


    const formData = {
      chkinDate: $('#chkinDate').val(),
      working_mode: $('#working_mode').val(),
      comments: $('#comments').val(),
      sys_prob: $('#sys_prob').is(':checked') ? 1 : 0
    };
    $.ajax({
      url: '/attendance_update_mode',
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('input[name="_token"]').val(),
        // 'X-HTTP-Method-Override': 'PUT'
      },
      data: formData,
      beforeSend: function () {
        $('#statusAttend').prop('disabled', true).text('Saving...');
        $('.sender_load').show();
      },
      success: function (response) {
        $('#success-message').text(response.message).fadeIn().delay(1000).fadeOut();
        $('#chatbot-icon').show();
        const modal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
        setTimeout(() => modal?.hide(), 500);
      },
      error: function (xhr) {
        const errorMsg = xhr.responseJSON?.message || 'Something went wrong';
        $('#error-message').text('Error: ' + errorMsg).fadeIn().delay(5000).fadeOut();
      },
      complete: function () {
        $('#statusAttend').prop('disabled', false).text('Save');
        $('.sender_load').hide();
      }
    });
    return false; // prevent default form submit
  }
});

 $('#irregularchkoutForm').validate({
      rules: {
        worked_period:  { required: true },
        chkoutTime: { required: true },
        chkout_reason: {
          required: function () {
            return $('#waiver_set').val() === '0';
          }
        }
      },
      messages: {
        worked_period:"Select the Worked Period",
        chkoutTime: "Select Checkout Time.",
        chkout_reason: "Enter the Comments."
      },
      errorClass: "is-invalid",
      errorElement: "label",
      errorPlacement: function (error, element) {
        element.after(error);
      },
      submitHandler: function (form) {
        const formData = $(form).serialize();
        $.ajax({
          url: '/attendance_update_irregular_chkout',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val(),
            // 'X-HTTP-Method-Override': 'PUT'
          },
          data: formData,
          beforeSend: function () {
            $('#chkoutAttend').prop('disabled', true).text('Saving...');
            $('.process_load').show();
          },
          success: function (response) {
            $('#success-message1').text(response.message).fadeIn().delay(3000).fadeOut();
            const modal = bootstrap.Modal.getInstance(document.getElementById('IrregularChkoutModal'));
            setTimeout(() => modal?.hide(), 3000);
             window.location.href = response.url;
          },
          error: function (xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Something went wrong';
            $('#error-message1').text('Error: ' + errorMsg).fadeIn().delay(5000).fadeOut();
          },
          complete: function () {
            $('#chkoutAttend').prop('disabled', false).text('Save');
            $('.process_load').hide();
          }
        });
        return false; // prevent default form submit
      }
});


/**For Report Generation - Attendance Report*/
// Only show date picker (no time) for startDate and endDate
$(document).ready(function () {
  $("#startDate").datepicker({
    dateFormat: "dd-mm-yy",
    onSelect: function (selectedDate) {
      var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate);
      var minDate = new Date(dateObj);
      minDate.setDate(minDate.getDate() + 1);
      $("#endDate").datepicker("option", "minDate", minDate);
     // $("#endDate").datepicker("show"); // Show endDate picker after selecting startDate
    }
  });
  $("#endDate").datepicker({
    dateFormat: "dd-mm-yy"
  });
});



// $('#worked_period').on('change', function () { 
//     const formData = {
//         worked_period: $('#worked_period').val(),
//     };

//     $.ajax({
//         url: '/chkdata_timesheet',
//         type: 'GET',
//         data: formData,
//           success: function (response) {
//               // if (response.url) {
//               //     window.location.href = response.url; // redirect
//               // }
//           },
//         error: function (xhr) {
//             const errorMsg = xhr.responseJSON?.message || 'Something went wrong';
//             $('#error-message').text('Error: ' + errorMsg).fadeIn().delay(5000).fadeOut();
//         },
//         complete: function () {
//             $('.sender_load').hide();
//             $('#IrregularChkoutModalLabel').modal('hide');
//         }
//     });
// });