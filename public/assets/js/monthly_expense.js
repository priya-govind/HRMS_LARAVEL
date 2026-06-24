
            document.getElementById('transaction_type').addEventListener('change', function () {
                // document.getElementById('credit-section').style.display = this.value === 'credit' ? 'block' : 'none';
                document.getElementById('debit-section').style.display = this.value === 'debit' ? 'block' : 'none';
                document.getElementById('debit-section1').style.display = this.value === 'debit' ? 'block' : 'none';
                $('#debit-section1 :input').prop('disabled', true);
                 if (this.value === 'credit') {
                    $('#total_amt :input').prop('disabled', false);
                } else {
                    $('#total_amt :input').prop('disabled', true);
                }
            });
            $('#expense_item_id').change(function() {
               $('#generate_expen_tool').show(); 
               $('#amount').prop('readonly', true);
            });

            const dateInput = document.querySelector('input[name="transaction_date"]');
            const today = new Date().toISOString().split('T')[0];
            dateInput.max = today;

            document.getElementById('filter_expenses').addEventListener('submit', function(e) {
                const inputs = this.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (!input.value) {
                        input.disabled = true; // Prevent empty fields from being submitted
                    }
                });
            });

            $('#addexpenseButton').click(function () {
                const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                    backdrop: 'static',
                    keyboard: false
                });
                myModal.show();
                $('#dataModalLabel').text('Add New Transaction');
                $('#expenseForm').trigger('reset');
                $('#recordId').val('');
                $('#bill-preview').hide();
                            document.getElementById('debit-section').style.display = this.value === 'debit' ? 'block' : 'none';
                            document.getElementById('debit-section1').style.display = this.value === 'debit' ? 'block' : 'none';
                             $('#expenseForm')
                                .find('input, select, textarea, button')
                                .prop('disabled', false);

                if (!$('#recordId').val()) {
                    $('#saveBtn').text('Add').show();
                }
            });
            $('#generate_expense').click(function() {
                $("#expense_details").empty(); // Clear previous entries
                let selectedexpense = $("#expense_item_id").val(); 
                    $.ajax({
                        url: `/monthly_expense/show`,
                        type: "GET",
                        data: { expense_ids: selectedexpense,
                        }, // Sending only extracted values
                        success: function(response1) { 
                          
                            Object.entries(response1).forEach(function([id, name]) {
                                    $("#expense_details").append(`
                                        <tr>
                                            <td><input type="hidden" name="sel_exp_id[]" value="${id}"> ${name}</td>
                                            <td><input type="number" name="sel_exp_amt[${id}]" placeholder="Enter the Amount" class="form-control"></td>
                                        </tr>
                                    `);
                                });
                            $('#indiv_expense').show(); 
                            $('#generate_expense').hide(); 
                            $('#debit-section1 :input').prop('disabled', false);
                            $('#total_amt :input').prop('disabled', false);
                           // $('#saveBtn').prop('disabled', false);              
                        }
                    });
            });
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
let debounceTimer;
$(document).on('input', 'input[name^="sel_exp_amt"]', function () {
    let total = 0;
    $('input[name^="sel_exp_amt"]').each(function () {
        const val = parseFloat($(this).val());
        if (!isNaN(val)) {
            total += val;
        }
    });

    $('#amount').val(total); // Update total field

    // Trigger AJAX validation
    clearTimeout(debounceTimer);
    const trans_type = $('#transaction_type').val();
    const recordId = $('#recordId').val();

    debounceTimer = setTimeout(function () {
        if (total !== "") {
            $.ajax({
                url: "/check-amount-available",
                method: "GET",
                data: {
                    amount: total,
                    trans_type: trans_type,
                    id: recordId
                },
                success: function (response) {
                    if (response.flag == 1) {
                        showInvalidFeedback("#amount", "Insufficient Balance");
                    } else {
                        hideInvalidFeedback("#amount");
                    }
                },
                error: function () {
                    console.error("Error checking amount.");
                }
            });
        }
    }, 300);
});
function showInvalidFeedback(selector, message) {
    const input = $(selector);
    input.addClass("is-invalid").removeClass("is-valid");

    // Remove existing feedback if any
    input.next(".invalid-feedback").remove();

    // Append new feedback
    input.after(`<div class="invalid-feedback">${message}</div>`);
}

function hideInvalidFeedback(selector) {
    const input = $(selector);
    input.removeClass("is-invalid").addClass("is-valid");
    input.next(".invalid-feedback").remove();
}
$(document).on('click', '.remove-expense-item', function () {
    $(this).closest('tr').remove();
});

        $(document).off('click', '.editexpenseButton').on('click', '.editexpenseButton', function () {
            const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                backdrop: 'static',
                keyboard: false
            });
            
            const id = $(this).data('id');
            const type = $(this).data('type');
            // Fetch data for the selected record
            $.get('/monthly_expense/' + id + '/edit', function (data) {
                $('#dataModalLabel').text('Edit Monthly Expense');
                $('#recordId').val(data.id);
                //resets validation logic applied for adding to set for edit
                $("#expenseForm label[id$='-error']").text("").removeClass("is-invalid");
                $("#expenseForm").find("input, select, textarea,label").removeClass("is-valid is-invalid");
                
                if(data.transaction_type=='credit'){
                        document.getElementById('debit-section').style.display = 'none';
                         document.getElementById('debit-section1').style.display ='none';   
                } else {
                 document.getElementById('debit-section').style.display = 'block' ;
                document.getElementById('debit-section1').style.display ='block';   
                }
                $('#transaction_type').val(data.transaction_type);
                if(data.transaction_type=='debit'){
                    $('#amount').prop('readonly', true);
                }
                $('#amount').val(data.amount);
                $('#transaction_date').val(data.transaction_date);
                $('#payment_type').val(data.payment_type);
                 $('#remarks').val(data.remarks);
                const selectElement = document.querySelector('select[name="items[][expense_item_id]"]');
                $(selectElement).val(data.items);
                        $.ajax({
                                url: '/get_details',
                                method: 'GET',
                                data: { expense_ids: data.id },
                                success: function (response1) {
                                    $("#expense_details").empty();   
                                    const items = response1.monthly_expense_details;
                                    const showDelete = items.length > 1;                 
                                       items.forEach(function (item) {
                                            const id = item.expense_item_id;
                                            const name = item.expense_type_name;
                                            const amount = item.exp_amount;

                                            $("#expense_details").append(`
                                                <tr>
                                                    <td><input type="hidden" name="sel_exp_id[]" value="${id}"> ${name}</td>
                                                    <td>
                                                   <div class="d-flex align-items-center">
                                                     <input type="number" name="sel_exp_amt[${id}]" value="${amount}" class="form-control" style="margin: 0 19px 0 0;">
                                                     ${showDelete ? '<button type="button" class="btn btn-sm btn-danger remove-expense-item">×</button>' : ''}
                                                  </div>
                                                   </td>
                                                </tr>
                                            `);
                                        });

                                        $('#indiv_expense').show();
                                        $('#debit-section1 :input').prop('disabled', false);
                                        $('#generate_expense').hide();
                                },
                                error: function () {
                                    console.error("Failed to fetch expense item details.");
                                }
                            });
                if(data.transaction_type=='debit'){
                    $('#debit-section').show();
                    $('#debit-section1').show();
                }
                if (data.bill_refer) {
                    $('#bill-preview').show();
                    $('#bill-image').attr('src', '/monthly_expenses/' + data.bill_refer);
                } else {
                    $('#bill-preview').hide();
                }
              $('#saveBtn').text('Update');
              if(type==='old'){
                   $('#expenseForm')
                    .find('input, select, textarea, button')
                    .prop('disabled', true);
              } else {
                    $('#expenseForm')
                    .find('input, select, textarea, button')
                    .prop('disabled', false);
              }
              

                myModal.show();
            });
        });
        // $('#viewImageBtn').on('click', function () {
        //     const imageUrl = $('#bill-image').attr('src'); // or set a static path
        //     $('#popupImage').attr('src', imageUrl);
        //     const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        //     imageModal.show();
        //     });
            
let currentImageUrl = '';

$(document).on('click', '#viewImageBtn', function () {
  currentImageUrl = $(this).data('image');
  $('#popupImage').attr('src', currentImageUrl);
  $('#senderEmail').val('');
  const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
  imageModal.show();
});

$('#mailImageBtn').on('click', function () {
  const senderEmail = $('#senderEmail').val().trim();

  if (!senderEmail) {
     $("#senderEmail").addClass("is-invalid").removeClass("is-valid");
     $("#senderEmail").next(".invalid-feedback").text("Please enter your email address.").show();
    return;
  } else {
     $("#senderEmail").addClass("is-valid").removeClass("is-invalid");
  }
   if (!isValidEmail(senderEmail)) {
     $("#senderEmail").addClass("is-invalid").removeClass("is-valid");
     $("#senderEmail").next(".invalid-feedback").text("Please enter Valid email address.").show();
    return;
  } else {
    $("#senderEmail").addClass("is-valid").removeClass("is-invalid");
  }

  $.ajax({
    url: '/send-image-mail',
    method: 'POST',
    data: {
      image: currentImageUrl,
      email: senderEmail,
      _token: $('meta[name="csrf-token"]').attr('content')
    },
    success: function () {
    alert('Image sent successfully!');
    $("#senderEmail").removeClass("is-valid");
    const imageModal = bootstrap.Modal.getInstance(document.getElementById('imageModal'));
    imageModal.hide();
    },
    error: function () {
      alert('Failed to send image.');
    }
  });
});

$(document).ready(function () {
    $("#expenseForm").validate({
        rules: {
            transaction_type: "required",
            amount: {
                required: true,
                number: true,
                min: 1
            },
            transaction_date: "required",
            payment_type: {
                required: function () {
                    return $("#transaction_type").val() === "debit";
                }
            },
            "items[][expense_item_id]": {
                required: function () {
                    return $("#transaction_type").val() === "debit";
                }
            },
             bill_refer:"required",

        },
        messages: {
            transaction_type: "Please select transaction type",
            amount: {
                required: "Please enter an amount",
                number: "Enter a valid number",
                min: "Amount must be greater than zero"
            },
            transaction_date: "Please select a date",
            payment_type: "Please select a payment type",
            "items[][expense_item_id]": "Please select at least one expense item",
             bill_refer:"Upload the Bill",
        },
        errorClass: "is-invalid",
        validClass: "is-valid",
        errorPlacement: function (error, element) {
            if (element.attr("name") === "items[][expense_item_id]") {
                error.insertAfter(element.closest(".mb-3"));
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            Submit_MonthlyExpenseForm();
        }
    });
});
$(document).on('click', '.delete-expense-btn', function () {
    const itemId = $(this).data('id'); // Get item ID from the button
    //const url = '/transactions/' + itemId + '/destroy';
    const url = '/monthly_expense/' + itemId; // Remove '/destroy'
    const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
    // Add click listener for the Delete button inside the modal
    $('#confirmDelete').off('click').on('click', function () {
        deleteItem(itemId,url); // Call your delete function
        myModal.hide();
    });
});

$(document).on('click', '.remove-expense-item', function () {
    $(this).closest('tr').remove();

    // Recalculate total
    let total = 0;
    $('input[name^="sel_exp_amt"]').each(function () {
        const val = parseFloat($(this).val());
        if (!isNaN(val)) {
            total += val;
        }
    });

    $('#amount').val(total); // Update total field

    // Trigger AJAX validation
    clearTimeout(debounceTimer);
    const trans_type = $('#transaction_type').val();
    const recordId = $('#recordId').val();

    debounceTimer = setTimeout(function () {
        if (total !== "") {
            $.ajax({
                url: "/check-amount-available",
                method: "GET",
                data: {
                    amount: total,
                    trans_type: trans_type,
                    id: recordId
                },
                success: function (response) {
                    if (response.flag == 1) {
                        showInvalidFeedback("#amount", "Insufficient Balance");
                    } else {
                        hideInvalidFeedback("#amount");
                    }
                },
                error: function () {
                    console.error("Error checking amount.");
                }
            });
        }
    }, 300);
});