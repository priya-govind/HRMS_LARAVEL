    $(document).ready(function () {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
// Add and Edit Modal Handling
$('#addButton').click(function () {
    const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
    $('#dataModalLabel').text('Add New Working Mode');
    $('#dataForm').trigger('reset');
    $('#recordId').val('');
});

$(document).off('click', '.editButton').on('click', '.editButton', function () {
    const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
    const id = $(this).data('id');

    // Fetch data for the selected record
    $.get('/working_mode/' + id + '/edit', function (data) {
        $('#dataModalLabel').text('Edit Working Mode');
        $('#recordId').val(data.id);
        $('#work_mode_name').val(data.work_mode_name);
        $('input[name="mode_status"][value="' + data.mode_status + '"]').prop('checked', true);
    });
});

    $.validator.addMethod(
        "pattern",
        function (value, element, param) {
            return this.optional(element) || new RegExp(param).test(value);
        },
        "Invalid format."
    );

    // Initialize validation
    $("#dataForm").validate({
        errorClass: "is-invalid", // Add Bootstrap's 'is-invalid' class to highlight errors
        rules: {
            work_mode_name: {
                required: true,
                pattern: "^[a-zA-Z0-9\\s]*",
            },
            mode_status: {
                required: true,
            },
        },
        messages: {
            work_mode_name: {
                required: "Working Mode name is required.",
                //pattern: "Special characters are not allowed in the Working Mode name.",
            },
            mode_status: {
                required: "Status is Required.",
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
        submitHandler: function (form) {
            // const formData = $(form).serialize();
             const id = $('#recordId').val();
             const method = id ? 'PUT' : 'POST';
             const url = $('#recordId').val() ? '/working_mode/' + $('#recordId').val() 
                                            : '/working_mode'; 
            let formData = $('#dataForm').serialize(); // collects all fields with name attributes

            // If this is an update, append _method=PUT for Laravel to handle it
            if ($('#recordId').val()) {
            formData += '&_method=PUT';
            }
            const myModal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
            const success='#success-message1';
            const fail='#error-message1';
            const DatatableId='#WorkModeTable';
            callajax_noreturn(url,'POST',formData,success,fail,myModal,DatatableId);
        },
    });
$(document).on('click', '.delete-btn', function () {
    const itemId = $(this).data('id'); // Get item ID from the button
    const url = '/working_mode/' + itemId ;
    const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
    // Add click listener for the Delete button inside the modal
    $('#confirmDelete').off('click').on('click', function () {
        const DatatableId='#WorkModeTable';
        deleteItem(itemId,url,DatatableId); 
        myModal.hide();
    });
});
// Confirmation Alert Box before Deleting
$(document).on('click', '.change_state-btn', function () {

const id = $(this).data('id');
const stat = $(this).data('type'); // Get the Type of the item
const url = '/working_mode/' + id + '/status_change';
const success='#success-message';
const fail='#error-message';
const DatatableId='#WorkModeTable';

     callajax_noreturn(url, 'POST', {
            _token: '{{ csrf_token() }}',
            status: stat,
        },success,fail,'',DatatableId);
});
$('#addexpensetypeButton').click(function () {
    const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
    $('#dataModalLabel').text('Add New Bill Type');
    $('#dataexpensetypeForm').trigger('reset');
    $('#recordId').val('');
});

$(document).off('click', '.editbilltypeButton').on('click', '.editbilltypeButton', function () {
    const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static',
        keyboard: false
    });
    
    const id = $(this).data('id');
    // Fetch data for the selected record
    $.get('/expenses/' + id + '/edit', function (data) {
        $('#dataModalLabel').text('Edit Bill Type');
        $('#recordId').val(data.id);
        $('#expense_type_name').val(data.expense_type_name);
        $('input[name="expense_type_status"][value="' + data.bill_typ_status + '"]').prop('checked', true);
        myModal.show();
    });
});
$("#dataexpensetypeForm").validate({
        errorClass: "is-invalid", // Add Bootstrap's 'is-invalid' class to highlight errors
        rules: {
            expense_type_name: {
                required: true,
                pattern: "^[a-zA-Z0-9\\s]*",
            },
            mode_status: {
                required: true,
            },
        },
        messages: {
            expense_type_name: {
                required: "Name of the bill type is required.",
                //pattern: "Special characters are not allowed in the Working Mode name.",
            },
            mode_status: {
                required: "Status is Required.",
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
        submitHandler: function (form) {
            // const formData = $(form).serialize();
             const id = $('#recordId').val();
             const method = id ? 'PUT' : 'POST';
             const url = $('#recordId').val() ? '/expenses/' + $('#recordId').val() 
                                            : '/expenses'; 
            let formData = $('#dataexpensetypeForm').serialize(); // collects all fields with name attributes
            // If this is an update, append _method=PUT for Laravel to handle it
            if ($('#recordId').val()) {
            formData += '&_method=PUT';
            }
            const myModal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
            const success='#success-message1';
            const fail='#error-message1';
            const DatatableId='#ExpenseTypeTable';
            callajax_noreturn(url,'POST',formData,success,fail,myModal,DatatableId);
        },
    });
$(document).on('click', '.delete-billtype-btn', function () {
    const itemId = $(this).data('id'); // Get item ID from the button
    const url = '/expenses/' + itemId ;
    const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
    // Add click listener for the Delete button inside the modal
    $('#confirmDelete').off('click').on('click', function () {
        const DatatableId='#ExpenseTypeTable';
        deleteItem(itemId,url,DatatableId); 
        myModal.hide();
    });
});
    });
    