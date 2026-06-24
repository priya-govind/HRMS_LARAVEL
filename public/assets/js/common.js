$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
function cancelReply() {
    const replyBox = document.getElementById('reply-box');
    if (replyBox) {
        replyBox.innerHTML = '';
        replyBox.style.display = 'none';
        delete replyBox.dataset.replyTo;
    }
}
function get_uri_segment(segment_no){
    var url = window.location.href;
    var segments = url.split("/");
    var segment = segments[segment_no];
   return segment;
}
/**Load Multiselect Drpdown Value */
function ajaxload(url, method, assign_id, additionalData,selectedValues='') {
    //alert(selectedValues);
    $.ajax({
        url: url,
        type: method,
        data: additionalData,
        async: true, // Explicitly setting async
        success: function(data) {
            $(assign_id).empty();
            $(assign_id).css('pointer-events', 'visible').prop('multiple', true); // Improved styling and attribute handling

            $.each(data, function(index, obj) {
                if(selectedValues)
                 var isSelected = selectedValues.includes(obj.id) ? 'selected' : '';
                $(assign_id).append('<option value="'+ obj.id +'">'+ obj.name +'</option>');
            });
            if(selectedValues)
            $(assign_id).css('pointer-events', 'none')
            $(assign_id).val(selectedValues).trigger('change');
        },
        error: function(xhr, status, error) {
            console.error("Error loading data:", error);
        }
    });
}
/**Load Multiselect Dropdown with checkbox */
function ajaxLoadSelectDropdown_Checkboxes(url, method, containerSelector, additionalData = {}, selectedValues = [], paramKey = 'ctrl_id') {
    const requestData = {
        ...additionalData,
        [paramKey]: selectedValues // dynamic key name!
    };

    $.ajax({
        url: url,
        type: method,
        data: requestData,
        success: function (data) {
            const $container = $(containerSelector).empty();
            const normalizedSelected = selectedValues.map(String);

            data.forEach(obj => {
                const isChecked = normalizedSelected.includes(String(obj.id)) ? 'checked' : '';
                const checkboxHtml = `
                    <label class="checkbox-item">
                        <input type="checkbox" class="roleCheckbox" name="${paramKey}[]" value="${obj.id}" ${isChecked}>
                        ${obj.name}
                    </label>
                `;
                $container.append(checkboxHtml);
            });
        },
        error: function (xhr, status, error) {
            console.error("Error loading checkboxes:", error);
        }
    });
}

function callajax_noreturn(url,method,data,success,fail,myModal='',DatatableId='',FormID='') {
     
    $.ajax({
                url: url,
                type: method,
                data: data,
                success: function (response) {
                    let messageContainer; 
                    if (response.success) {
                        messageContainer = $(success); // Select the container for the message
                        messageContainer.text(response.success).show(); // Update and display the message
                        $(FormID).trigger('reset');
                    }

                    if (response.failure) {
                        messageContainer = $(fail); // Select the container for the message
                        messageContainer.text(response.failure).show(); // Update and display the message
                        $(FormID).trigger('reset');
                    }

                    // Hide the message after 2 seconds
                    if (messageContainer) { // Only if it was set
                        setTimeout(function () {
                            messageContainer.hide();

                            if (myModal) {
                                myModal.hide();
                            }
                            if (DatatableId) {
                                var table = $(DatatableId).DataTable();
                                table.ajax.reload(null, false);
                            }
                        }, 2000);
                    }
    },
      error: function (xhr) {
        const messageContainer = $(fail); // Select the container for the error message
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            messageContainer.text('Error: ' + errorMessage).show(); // Update and display the error message
            // Hide the error message after 5 seconds
            setTimeout(function () {
                messageContainer.hide();
              
            }, 4000);
      }
            });
}
function callajax_nullparamas(url, modalElement,redirect_url=''){
    $.ajax({
        url: url,
        success: function (response) {
            //alert(JSON.stringify(modalElement));
            if (modalElement) {
                const myModal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                $('.modal-body').text(response.message);
                myModal.show();

                setTimeout(function () {
                    myModal.hide();
                    if(redirect_url!='')
                        window.location.href = redirect_url;
                }, 2500);
            }
        },
        error: function (xhr) {
            const messageContainer = $('#failMessage'); // Replace with your actual error container selector
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            messageContainer.text('Error: ' + errorMessage).show();

            setTimeout(function () {
                messageContainer.hide();
            }, 2000);
        }
    });
}
function callajax_return(url,modalElement){
    $.ajax({
        url: url,
        success: function (response) {
           
            //alert(JSON.stringify(response));
            if (modalElement) {
                const myModal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });
                $('.modal-body').text(response.message);
                myModal.show();
                if(get_uri_segment(3)=='attendance_info'){
                    document.getElementById('taskModal').style.display = 'none';
                }
                setTimeout(function () {
                    myModal.hide();
                    if(response.redirect_url!='')
                        window.location.href = response.redirect_url;
                }, 3000);
            }
        },
        error: function (xhr) {
            const messageContainer = $('#failMessage'); // Replace with your actual error container selector
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            messageContainer.text('Error: ' + errorMessage).show();

            setTimeout(function () {
                messageContainer.hide();
            }, 2000);
        }
    });
}
function callajax_popup(url,modalElement,msg){
    const myModal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
    });
    $('.modal-body').text(msg);
    myModal.show();

    setTimeout(function () {
        myModal.hide();
        if(url!='')
            window.location.href = url;
    }, 3000);
}
// Example delete function
function deleteItem(id,url,table_id) {
    $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: $('input[name="_token"]').val()
                    },
                success: function (response) {
        const messageContainer = $('#success-message'); // Select the container for the message
            messageContainer.text(response.message).show(); // Update and display the message

            // Hide the message after 5 seconds
            setTimeout(function () {
                messageContainer.hide();
                var table = $(table_id).DataTable();
                   table.ajax.reload(null, false);
            }, 2000);
         // // Refresh page
      },
      error: function (xhr) {
        const messageContainer = $('#error-message'); // Select the container for the error message
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            messageContainer.text('Error: ' + errorMessage).show(); // Update and display the error message

            // Hide the error message after 5 seconds
            setTimeout(function () {
                messageContainer.hide();
            }, 4000);

      }
            });
}
function isTimeGreaterThan915(timeStr) {
  if (!timeStr) return false;

  // Convert "12:38 PM" to 24-hour format
  const timeParts = timeStr.match(/(\d+):(\d+)\s*(AM|PM)/i);
  if (!timeParts) return false;

  let hours = parseInt(timeParts[1], 10);
  const minutes = parseInt(timeParts[2], 10);
  const period = timeParts[3].toUpperCase();

  if (period === 'PM' && hours !== 12) hours += 12;
  if (period === 'AM' && hours === 12) hours = 0;

  const inputMinutes = hours * 60 + minutes;
  const thresholdMinutes = 9 * 60 + 15;

  const result = inputMinutes > thresholdMinutes;
  //console.log(`Check-in time: ${timeStr}, converted: ${hours}:${minutes}, is late: ${result}`);
  return result;
}
function shouldRequireComments(timeStr) {
  if (!timeStr) return false;

  const timeParts = timeStr.match(/(\d+):(\d+)\s*(AM|PM)/i);
  if (!timeParts) return false;

  let hours = parseInt(timeParts[1], 10);
  const minutes = parseInt(timeParts[2], 10);
  const period = timeParts[3].toUpperCase();

  // Convert to 24-hour format
  if (period === 'PM' && hours !== 12) hours += 12;
  if (period === 'AM' && hours === 12) hours = 0;

  const inputMinutes = hours * 60 + minutes;

  // 9:15 AM in minutes
  const thresholdMinutes = 9 * 60 + 15;

  // Current time in minutes
//   const now = new Date();
//   const currentMinutes = now.getHours() * 60 + now.getMinutes();

  // Require comments if check-in is after 9:15 OR before current time
  //const requireComments = inputMinutes > thresholdMinutes || inputMinutes < currentMinutes;
 // const requireComments = inputMinutes > thresholdMinutes || inputMinutes < thresholdMinutes;
const requireComments = inputMinutes > thresholdMinutes;

  //console.log(`Check-in: ${timeStr}, Minutes: ${inputMinutes}, Now: ${currentMinutes}, Require comments: ${requireComments}`);
  return requireComments;
}
function formatTo12Hour(timeStr) {
  const [hour, minute] = timeStr.split(':').map(Number);
  const ampm = hour >= 12 ? 'pm' : 'am';
  const hour12 = hour % 12 || 12;
  return `${hour12}:${String(minute).padStart(2, '0')}${ampm}`;
}
function convertTo24Hour_permission_time(timeStr) {
  const [time, modifier] = timeStr.split(' ');
  let [hours, minutes] = time.split(':');
  hours = parseInt(hours, 10);
  if (modifier === 'PM' && hours !== 12) hours += 12;
  if (modifier === 'AM' && hours === 12) hours = 0;
  return `${String(hours).padStart(2, '0')}:${minutes}:00`;
}
function formatTo12Hour_permission_time(date) {
  let hours = date.getHours();
  const minutes = date.getMinutes();
  const ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12 || 12;
  return `${hours}:${String(minutes).padStart(2, '0')} ${ampm}`;
}
function isValidEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}
function checkUserCheckout(userId) {
    return $.ajax({
        url: '/user/' + userId + '/checkout-status',
        type: 'GET'
    });
}

$(document).ready(function () {
    /**Notification bellicon count */
    $('#notificationDropdown').click(function(){
                $.ajax({
                    url: `/notifications/make_read`,
                    type: "GET",
                    success: function(response) {
                        $('#notificationCount').text('0');
                    }
                });
        });
    $('#chkout_user').click(function () {
        const url = '/checkout';
        const modalElement = document.getElementById('messageModal');
        $.ajax({
            url: url,
            success: function (response) {
                //alert(JSON.stringify(response));
                if(response.checkout){
                        $('.modal-body').text(response.message);
                        const myModal = new bootstrap.Modal(modalElement);
                        myModal.show();
                        setTimeout(() => {
                            myModal.hide();
                        //if (response.redirect_url) window.location.href = response.redirect_url;
                        }, 3000);  
                }else {
                   $('#redirect_url').val(response.redirect_url);
                     const earlyModal = new bootstrap.Modal(document.getElementById('earlyCheckoutModal'), {
                                     backdrop: 'static',
                                    keyboard: false
                                });
                                earlyModal.show();
                }
            }
        });
    });
    $(document).on('click', '.show_permit_popup', function() {
        if($(this).val()==='no'){
            $('#permit_form').addClass('d-none').removeClass('d-block');
            $('#show_footer').addClass('d-none').removeClass('d-block');
            window.location.href = $('#redirect_url').val();
        } else {
            $('#permit_form').addClass('d-block').removeClass('d-none');
            $('#show_footer').addClass('d-block').removeClass('d-none');
        }
        return false;
    });
    $("#checkout_form").validate({
        rules: {
            leave_type: {
                required: true,
            },
            earlyReasonInput: {
                required: true,
            },
        },
        messages: {
            leave_type: {
                required: "Select the Leave type",
            },
            earlyReasonInput: {
                required: "Enter the reason",
            },
        },
        highlight: function (element, errorClass) {
            if ($(element).attr("name") === "leave_type") {
                // Add error class to the group container instead of the radio itself
                $(element).closest(".m-3").addClass(errorClass);
            } else {
                $(element).addClass(errorClass);
            }
        },
        unhighlight: function (element, errorClass) {
            if ($(element).attr("name") === "leave_type") {
                $(element).closest(".m-3").removeClass(errorClass);
            } else {
                $(element).removeClass(errorClass);
            }
        },
        errorPlacement: function (error, element) {
            if (element.attr("name") === "leave_type") {
                // Place error after the whole radio group container
                error.appendTo(element.closest(".mb-3"));
            } else {
                error.appendTo(element.parent());
            }
        },
        submitHandler: function (form) {
            const url = "/update_checkout_status";
            let formData = $("#checkout_form").serialize(); // collects all fields with name attributes
            formData += "&_method=POST";
            const success = "#success-message3";
            const fail = "#error-message3";
            const DatatableId = "#categoryTable";
            const myModal = bootstrap.Modal.getInstance(
                document.getElementById("earlyCheckoutModal"),
            );
            callajax_noreturn(
                url,
                "POST",
                formData,
                success,
                fail,
                myModal,
                DatatableId,
            );
            //    setTimeout(function () {
            //             $('#checkout_form')[0].reset();
            //     }, 2000);
        },
    });
});