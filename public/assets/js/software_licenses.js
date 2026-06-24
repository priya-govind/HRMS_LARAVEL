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
            $('#dataModalLabel').text('Add New Software Lincense Type Name');
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
            $.get('/software_licenses/' + id + '/edit', function (data) {
                $('#dataModalLabel').text('Edit Software Lincense Type Name');
                $('#recordId').val(data.id);
                $('#license_type_name').val(data.license_type_name);
                $('input[name="license_type_status"][value="' + data.license_type_status + '"]').prop('checked', true);
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
                license_type_name: {
                    required: true,
                    pattern: "^[a-zA-Z0-9\\s]*",
                },
                license_type_status: {
                    required: true,
                },
            },
            messages: {
                license_type_name: {
                    required: "Software Lincense Type Name is required.",
                    //pattern: "Special characters are not allowed in the Software Lincense Type Name name.",
                },
                license_type_status: {
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
                const url = $('#recordId').val() ? '/software_licenses/' + $('#recordId').val() 
                                                : '/software_licenses'; 
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
            const url = '/software_licenses/' + itemId ;
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
    });
    