    // Ensure jQuery is ready
    $(document).ready(function () {
// Add and Edit Modal Handling
$('#addButton').click(function () {
    const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
    $('#dataModalLabel').text('Add New Category');
    $('#dataForm').trigger('reset');
    $('#recordId').val('');
});

$(document).off('click', '.editButton').on('click', '.editButton', function () {
        var validator = $("#dataForm").validate();

        validator.resetForm();

        // Also remove error classes from inputs
        $("#dataForm").find(".error").removeClass("error");
        $("#dataForm").find(".is-invalid").removeClass("is-invalid");
        $('#dataForm')[0].reset(); 
    const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
        backdrop: 'static',
        keyboard: false
    });
    myModal.show();
      
    const id = $(this).data('id');

    // Fetch data for the selected record
    $.get('/categories/' + id + '/edit', function (data) {
        $('#dataModalLabel').text('Edit Category');
        $('#recordId').val(data.id);
        $('#category_name').val(data.category_name);
        $('#url_link').val(data.url_link);
       // $('#support_access').val(data.support_access);
        $('#parent_id').val(data.parent_id || '');
        $('input[name="is_active_cat"][value="' + data.is_active_cat + '"]').prop('checked', true);
        $('input[name="support_access"][value="' + data.support_access + '"]').prop('checked', true);
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
                    category_name: {
                        required: true,
                        pattern: "^[a-zA-Z0-9\\s]*",
                    },
                    url_link: { 
                        required: true, 
                        pattern: "^[A-Za-z_]+$" 
                    },
                    parent_id: {
                        required: true,
                    },
                    is_active_cat: {
                        required: true,
                    }, 
                },
                messages: {
                    category_name: {
                        required: "Category name is required.",
                        pattern: "Special characters are not allowed in the category name.",
                    },
                    url_link: {
                        required: "Category URL is required.",
                        pattern: "Only alphabets and underscores are allowed (no spaces)." 
                    },
                    parent_id: {
                        required: "Please select a parent category.",
                    },
                    is_active_cat: {
                        required: "Please select a category status.",
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
                    const url = $('#recordId').val() ? '/categories/' + $('#recordId').val() : '/category/store';
                    const method = $('#recordId').val() ? 'PUT' : 'POST';
                    const myModal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
                    // Submit the form via AJAX
                    $.ajax({
                        url: url,
                        type: method,
                        data: formData,
                        beforeSend: function () {
                            $('#saveBtn').prop('disabled', true).text('Saving...');
                        },
                        success: function (response) {
                            $('#success-message1').text(response.success).fadeIn().delay(5000).fadeOut();
                        
                            // Delay page reload
                            setTimeout(function () {
                                myModal.hide();
                                $('#categoryTable').DataTable().ajax.reload(null, false);
                                
                                //location.reload();
                            }, 3000); // Delay by 500ms to allow the modal to close
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
            const url = '/categories/' + itemId + '/destroy';
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
        const url = '/categories/' + id + '/status_change';

        // Confirm dialog
        $.ajax({
            url: url,
            type: 'POST',
            data: {
               // _token: '{{ csrf_token() }}',
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
