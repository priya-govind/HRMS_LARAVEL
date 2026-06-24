  $(document).ready(function () { 
     const sendModal = new bootstrap.Modal(document.getElementById('sendReportModal'), {
                                            backdrop: 'static',
                                            keyboard: false
                                        });
        $("#FromDate").datepicker({
      dateFormat: "dd-mm-yy",
      onSelect: function (selectedDate) {
        var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate);
        var minDate = new Date(dateObj);
        minDate.setDate(minDate.getDate() + 1);
        $("#ToDate").datepicker("option", "minDate", minDate);
      }
    });
    $("#ToDate").datepicker({
      dateFormat: "dd-mm-yy"
    }); 
       $('#search_project_id').change(function() {
              var proj_id = $(this).val();

              if(proj_id) {
                  $.ajax({
                      url: '/get_project_modules/' + proj_id,
                      type: 'GET',
                      success: function(data) {
                          $('#search_module_id').empty();
                          $('#search_module_id').append('<option value="">Select Module</option>');
                          $.each(data, function(id, name) {
                              $('#search_module_id').append('<option value="' + id + '">' + name + '</option>');
                          });
                      }
                  });
              } else {
                  $('#search_module_id').empty();
                  $('#search_module_id').append('<option value="">Select Module</option>');
              }
          });
    $('#search_module_id').change(function() {
             var proj_id = $('#search_project_id').val();
                  var module_id = $('#search_module_id').val();
                  if(module_id) {
                      $.ajax({
                          url: '/employees/modules/'+ module_id,
                          type: 'GET',
                              data: {
                                proj_id:proj_id,
                                module_id: module_id,
                                  },
                          success: function(data) {
                              $('#drp_emp_id').empty();
                               $('#drp_emp_id').append('<option value="">Select Employee Name</option>');
                              $.each(data, function(id, name) {
                                  $('#drp_emp_id').append('<option value="' + id + '">' + name + '</option>');
                              });
                          }
                      });
                  } else {
                      $('#drp_emp_id').empty();
                      $('#drp_emp_id').append('<option value="">Select Employee</option>');
                  }
          });
    $('#dataForm').on('submit', function (e) {
        e.preventDefault();
        if ($(this).valid()) {
            $('#categoryTable').DataTable().ajax.reload(null, false);
        }
    });
   $("input:radio[name=status_type]").change(function () {
    const stat = $(this).val();
    $('#drp_emp_id').prop('selectedIndex', 0);

    if (stat == 2) {
      $('.emp_stat').show();
    } else {
      $('.emp_stat').hide();
      $('#employee_id').val($('#user_id').val());
      //if (calendar) calendar.refetchEvents();
    }
  });
        $("#dataForm").validate({
        errorClass: "is-invalid", // Add Bootstrap's 'is-invalid' class to highlight errors
        rules: {
            FromDate: {
                required: true,
            },
            ToDate: {
                required: true,
            }
        },
        messages: {
            FromDate: {
                required: "From Date is required.",
            },
            ToDate: {
                required: "To Date is required.",
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
        
      });

    var table = $('#categoryTable').DataTable() ;
$("input[name='status_type']").on("change", function() {
    const statusType = $("input[name='status_type']:checked").val();
    if (statusType == "2") {
        table.column(2).visible(true);
         $('#categoryTable tfoot th:first').attr('colspan',10);
        $('#categoryTable tfoot th:last').remove();

    } else {
        table.column(2).visible(false);
         $('#categoryTable tfoot th:first').attr('colspan', 9);
        $('#categoryTable tfoot th:last').remove();

    }
});
// Show modal when clicking Send Report
$('#sendReportBtn').on('click', function () {
    sendModal.show();
});

// Confirm send inside modal
$('#confirmSendReport').on('click', function () {
    let recipients = $('#recipients').val();
    let fromDate   = $('#FromDate').val();
    let toDate     = $('#ToDate').val();
    let projId     = $('#search_project_id').val();
    let moduleId   = $('#search_module_id').val();
    let empId      = $('#drp_emp_id').val();
    let statusType = $("input[name='status_type']:checked").val();

    if (!recipients || recipients.length === 0) {
        alert("Please select at least one recipient.");
        return;
    }

    $.ajax({
        url: '/timesheet/send-report',
        method: 'POST',
        data: {
            recipients: recipients,
            FromDate: fromDate,
            ToDate: toDate,
            proj_id: projId,
            module_id: moduleId,
            emp_id: empId,
            status_type: statusType,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {
            alert(res.message);
            sendModal.hide();
        },
        error: function (xhr) {
            alert("Error sending report: " + xhr.responseText);
        }
    });
});
  });