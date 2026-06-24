    // Ensure jQuery is ready
    $(document).ready(function () {
// Add and Edit Modal Handling
$('#addButton').click(function () {
    const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
    $('#dataModalLabel').text('Add New Problem Type');
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
    $.get('/problem_types/' + id + '/edit', function (data) {
        $('#dataModalLabel').text('Edit Problem Type');
        $('#recordId').val(data.id);
        $('#ticket_type_id').val(data.ticket_type_id || '');
        $('#problem_type').val(data.problem_type);
        $('input[name="problem_type_active"][value="' + data.problem_type_active + '"]').prop('checked', true);
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
                    ticket_type_id:{
                         required: true, 
                    },
                    problem_type: {
                        required: true,
                        pattern: "^[a-zA-Z0-9\\s]*",
                    },
                    problem_type_active: {
                        required: true,
                    },
                },
                messages: {
                    ticket_type_id:{
                        required: "Please select a Ticket Type.",
                    },
                    problem_type: {
                        required: "Problem Type name is required.",
                        pattern: "Special characters are not allowed in the Problem Type name.",
                    },
                    problem_type_active: {
                        required: "Please select a Problem Type status.",
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
                    const isEdit = $('#recordId').val();
                    const url = isEdit ? '/problem_types/' + $('#recordId').val() : '/problem_types';
                    const method = 'POST'; // Always POST, use _method for PUT

                    const formData = new FormData(form);
                    if (isEdit) {
                        formData.append('_method', 'PUT');
                    }

                    const myModal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));

                    $.ajax({
                        url: url,
                        type: method,
                        data: formData,
                        processData: false, // Important for FormData
                        contentType: false, // Important for FormData
                        beforeSend: function () {
                            $('#saveBtn').prop('disabled', true).text('Saving...');
                        },
                        success: function (response) {
                            $('#success-message1').text(response.success).fadeIn().delay(5000).fadeOut();
                            setTimeout(function () {
                                myModal.hide();
                                location.reload();
                            }, 3000);
                        },
                        error: function (xhr) {
                            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                            $('#error-message1').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
                        },
                        complete: function () {
                            $('#saveBtn').prop('disabled', false).text('Save');
                        },
                    });
                },
            });
        });
        $(document).on('click', '.delete-btn', function () {
            const itemId = $(this).data('id'); // Get item ID from the button
            const url = '/problem_types/' + itemId ;
            const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    myModal.show();
            // Add click listener for the Delete button inside the modal
            $('#confirmDelete').off('click').on('click', function () {
                deleteItem(itemId,url,'#categoryTable'); // Call your delete function
                myModal.hide();
            });
        });

        // Confirmation Alert Box before Deleting
        $(document).on('click', '.change_state-btn', function () {
        const id = $(this).data('id');
        const stat = $(this).data('type'); // Get the Type of the item
        const url = '/problem_types/' + id + '/status_change';
        // Confirm dialog
        $.ajax({
            url: url,
            // type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: stat 
            },
            success: function (response) {
                const messageContainer = $('#success-message'); // Select the container for the message
                    messageContainer.text(response.message).show(); // Update and display the message
                    // Hide the message after 5 seconds
                    setTimeout(function () {
                        messageContainer.hide();
                        location.reload();
                    }, 1000);
                // // Refresh page
            },
            error: function (xhr) {
                const messageContainer = $('#error-message'); // Select the container for the error message
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    messageContainer.text('Error: ' + errorMessage).show(); // Update and display the error message
                    // Hide the error message after 5 seconds
                    setTimeout(function () {
                        messageContainer.hide();
                    }, 2000);
            }
        });
        });
    });
