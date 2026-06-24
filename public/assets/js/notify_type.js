    $(document).ready(function () {

// Add and Edit Modal Handling
$('#addButton').click(function () {
    const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
    $('#dataModalLabel').text('Add New Notification Type');
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
    $.get('/notify_type/' + id + '/edit', function (data) {
        $('#dataModalLabel').text('Edit Notification Type');
        $('#recordId').val(data.id);
        $('#notify_type').val(data.notify_type);
    });
});

// Handle Form Submission with Validation
$(document).ready(function () {
    // Add custom validator for 'pattern'
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
            notify_type: {
                required: true,
                pattern: "^[a-zA-Z0-9\\s]*",
            },
        },
        messages: {
            notify_type: {
                required: "Notification Type name is required.",
                pattern: "Special characters are not allowed in the notification name.",
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
            const formData = $(form).serialize();
            const url = $('#recordId').val() ? '/notify_type/' + $('#recordId').val() : '/notify_type/store';
            const method = $('#recordId').val() ? 'PUT' : 'POST';
            const myModal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
            const success='#success-message1';
            const fail='#error-message1';
            const DatatableId='#categoryTable';
            callajax_noreturn(url,method,formData,success,fail,myModal,DatatableId);
        },
    });
});

$(document).on('click', '.delete-btn', function () {
    const itemId = $(this).data('id'); // Get item ID from the button
    const url = '/notify_type/' + itemId + '/destroy';
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

// Confirmation Alert Box before Deleting
$(document).on('click', '.change_state-btn', function () {

const id = $(this).data('id');
const stat = $(this).data('type'); // Get the Type of the item
const url = '/notify_type/' + id + '/status_change';
const success='#success-message';
const fail='#error-message';
const DatatableId='#categoryTable';

     callajax_noreturn(url, 'POST', {
            _token: '{{ csrf_token() }}',
            status: stat,
        },success,fail,'',DatatableId);
});
    });