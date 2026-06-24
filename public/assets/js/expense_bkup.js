
            document.getElementById('transaction_type').addEventListener('change', function () {
                // document.getElementById('credit-section').style.display = this.value === 'credit' ? 'block' : 'none';
                document.getElementById('debit-section').style.display = this.value === 'debit' ? 'block' : 'none';
                document.getElementById('debit-section1').style.display = this.value === 'debit' ? 'block' : 'none';
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

$("#amount").on("input", function () {
    clearTimeout(debounceTimer);
    let amt = $(this).val();
    const trans_type = $('#transaction_type').val();
    const recordId = $('#recordId').val(); // Capture the ID for edit mode

    debounceTimer = setTimeout(function () {
        if (amt !== "") {
            $.ajax({
                url: "/check-amount",
                method: "GET",
                data: {
                    amount: amt,
                    trans_type: trans_type,
                    id: recordId // Pass ID to backend for edit context
                },
                success: function (response) {
                    if (response.flag == 1) {
                        $("#amount")
                            .addClass("is-invalid")
                            .removeClass("is-valid");
                        $("#amount").next(".invalid-feedback").text("Insufficient Balance");
                        $('.invalid-feedback').show();
                    } else {
                        $("#amount")
                            .addClass("is-valid")
                            .removeClass("is-invalid");
                        $('.invalid-feedback').hide();
                    }
                },
                error: function () {
                    console.error("Error checking amount.");
                }
            });
        }
    }, 300); // 300ms debounce
});
        $(document).off('click', '.editexpenseButton').on('click', '.editexpenseButton', function () {
            const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                backdrop: 'static',
                keyboard: false
            });
            
            const id = $(this).data('id');
            const type = $(this).data('type');
            // Fetch data for the selected record
            $.get('/transactions/' + id + '/edit', function (data) {
                $('#dataModalLabel').text('Edit Expense');
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
                $('#amount').val(data.amount);
                $('#transaction_date').val(data.transaction_date);
                $('#payment_type').val(data.payment_type);
                 $('#remarks').val(data.remarks);
                const selectElement = document.querySelector('select[name="items[][expense_item_id]"]');
                $(selectElement).val(data.items);

                if(data.transaction_type=='debit'){
                    $('#debit-section').show();
                    $('#debit-section1').show();
                }
                if (data.bill_refer) {
                    $('#bill-preview').show();
                    $('#bill-image').attr('src', '/bills/' + data.bill_refer);
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

let pdfDoc = null;
let pageNum = 1;
let scale = 1;

function renderPage(num) {
  pdfDoc.getPage(num).then(function(page) {
    const viewport = page.getViewport({ scale: scale });
    const canvas = document.getElementById('popupPdf');
    const ctx = canvas.getContext('2d');

    canvas.height = viewport.height;
    canvas.width = viewport.width;

    page.render({ canvasContext: ctx, viewport: viewport });
  });
}

$(document).on('click', '#viewImageBtn', function () {
  const currentFileUrl = $(this).data('image');
    $('#senderEmail').val('');

  if (currentFileUrl.toLowerCase().endsWith('.pdf')) {
    // Show PDF
    $('#popupImage').addClass('d-none').attr('src', '');
    $('#popupPdf').show();

    pdfjsLib.getDocument(currentFileUrl).promise.then(function(pdf) {
      pdfDoc = pdf;
      pageNum = 1;
      scale = 1;
      renderPage(pageNum);
    });
  } else {
    // Show Image
    $('#popupPdf').hide();
    $('#popupImage').removeClass('d-none').attr('src', currentFileUrl)
                   .css('transform', 'scale(1)');
    pdfDoc = null;
    scale = 1;
  }

  const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
  imageModal.show();
});
$('#zoomInBtn').click(function () {
  scale += 0.2;
  if (pdfDoc) {
    renderPage(pageNum);
  } else {
    $('#popupImage').css('transform', 'scale(' + scale + ')');
  }
});

$('#zoomOutBtn').click(function () {
  if (scale > 0.4) scale -= 0.2;
  if (pdfDoc) {
    renderPage(pageNum);
  } else {
    $('#popupImage').css('transform', 'scale(' + scale + ')');
  }
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
            // bill_refer:"required",

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
            // bill_refer:"Upload the Bill",
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
            submitExpenseForm();
        }
    });
});
$(document).on('click', '.delete-expense-btn', function () {
    const itemId = $(this).data('id'); // Get item ID from the button
    //const url = '/transactions/' + itemId + '/destroy';
    const url = '/transactions/' + itemId; // Remove '/destroy'
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