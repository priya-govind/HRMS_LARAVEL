$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
            backdrop: 'static',
            keyboard: false
        });
    // Add and Edit Modal Handling
    $('#addButton').click(function () {
        myModal.show();
        $('#dataModalLabel').text('Add New Attribute');
        $('#dataForm').trigger('reset');
        $('#recordId').val('');
    });

    // Add new attribute option field
$('#add-attribute').click(function () {
  if ($("#dynamic-fields .field-group").length < 5) {
  let  present_val=$("#dynamic-fields .field-group").length+1;
    const newFieldGroup = `
      <div class="field-group multiple_add_group">
        <div class="multiple_add">
          <input type="text" name="attribute_options[`+present_val+`]" class="attribute_options" id="attribute_options`+present_val+`" placeholder="Enter Option values for Attribute">
        </div>
        <div class="multiple_add_remove">
          <button type="button" class="btn btn-danger remove-field">Remove</button>
        </div>
        <hr>
      </div>`;
    $("#dynamic-fields").append(newFieldGroup);

    // Register the new field with validator
    const newField = $("#dynamic-fields .field-group").last().find("input.attribute_options");
    newField.rules("add", {
      required: true,
      messages: { required: "Please Enter Option for Attribute" }
    });
  } else {
        $('#error-message1')
            .text(`You can add a maximum of 5 fields.`)
            .fadeIn()
            .attr('tabindex', '-1')
            .focus()
            .delay(5000)
            .fadeOut();
    }
});

    // Custom pattern validation method
    $.validator.addMethod(
        "pattern",
        function (value, element, param) {
            return this.optional(element) || new RegExp(param).test(value);
        },
        "Invalid format."
    );

    // Initialize validation
    $("#dataForm").validate({
        rules: {
            attribute_name: {
                required: true,
                pattern: "^[a-zA-Z0-9\\s]*",
            },
            attribute_status: {
                required: true,
            },
            'attribute_options[]': {
                required: true
            }
        },
        messages: {
            attribute_name: {
                required: "Attribute Name is Required.",
            },
            attribute_status: {
                required: "Status is Required.",
            },
            'attribute_options[]': {
                required: "Please enter Attribute Value."
            }
        },
        highlight: function (element, errorClass) {
            if ($(element).attr("name") === "attribute_status") {
                // Add error class to the group container instead of the radio itself
                $(element).closest(".mb-3").addClass(errorClass);
            } else {
                 $(element).addClass(errorClass);
            }
        },
        unhighlight: function (element, errorClass) {
           if ($(element).attr("name") === "attribute_status") {
                $(element).closest(".mb-3").removeClass(errorClass);
            } else {
                $(element).removeClass(errorClass);
            }
        },
        errorPlacement: function (error, element) {
         if (element.hasClass("attribute_options")) {
            error.insertAfter(element); // show message after each option field
        } else if (element.attr("name") === "attribute_status") {
            // Place error after the whole radio group container
            error.appendTo(element.closest(".mb-3"));
        } else {
        error.appendTo(element.parent());
        }

        },
        submitHandler: function(form) {
                const id = $('#recordId').val();
                const method = id ? 'PUT' : 'POST';
                const recordId = $('#recordId').val();
                const url = ( recordId && recordId !=='new' ) ? '/update_attribute'
                                                : 'add_attribute/'; 
                let formData =  $('#dataForm').serialize(); // collects all fields with name attributes

                // If this is an update, append _method=PUT for Laravel to handle it
                if ($('#recordId').val()) {
                formData += '&_method=PUT';
                }
                const success='#success-message1';
                const fail='#error-message1';
                const DatatableId='#WorkModeTable';
                const myModal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
                callajax_noreturn(url,'POST',formData,success,fail,myModal,DatatableId);
                           setTimeout(function () {
                                    myModal.hide();
                                    $('#dataForm')[0].reset();
                                        $('#dynamic-fields').empty();
                                        $('#dynamic-fields').append(`
                                                <div class="field-group multiple_add_group">
                                                    <div class="multiple_add">
                                                        <input type="text" name="attribute_options[]" 
                                                            class="attribute_options form-control" 
                                                            placeholder="Enter Option values for Attribute">
                                                    </div>
                                                    <div class="multiple_add_remove">
                                                        <button type="button" class="btn btn-danger remove-field">Remove</button>
                                                    </div>
                                                    <hr>
                                                </div>
                                            `);
                            }, 2000);
           

            }
    });
     $("#dataForm").submit(function (e) {
            let isValid = true;
            $('.attribute_options').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('error');
                } else {
                    $(this).removeClass('error');
                }
            });
            if (!isValid) {
                e.preventDefault(); // Prevent form submission
            } 
        });
        // Edit Button Click Handling
        $(document).off('click', '.editButton').on('click', '.editButton', function () {
            const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
            const id = $(this).data('id');

            // Fetch data for the selected record
            $.get('/edit_attribute/' + id , function (data) {
                $('#dataModalLabel').text('Edit Attribute');
                $('#recordId').val(data.items.id);
                $('#attribute_name').val(data.items.attribute_name);
                $('input[name="attribute_status"][value="' + data.items.attribute_status + '"]').prop('checked', true);
                // Clear existing option fields
                    $('#dynamic-fields').empty();

                    // Loop through options and append inputs
                    data.options.forEach(function(opt, index) {
                        const newFieldGroup = `
                            <div class="field-group multiple_add_group">
                                <div class="multiple_add">
                                    <input type="text" name="attribute_options[]" 
                                        class="attribute_options form-control" 
                                        id="attribute_options${index+1}" 
                                        value="${opt.attribute_options}" 
                                        placeholder="Enter Option values for Attribute">
                                </div>
                                <div class="multiple_add_remove">
                                    <button type="button" class="btn btn-danger remove-field" id="${opt.id}">Remove</button>
                                </div>
                                <hr>
                            </div>`;
                        $('#dynamic-fields').append(newFieldGroup);
                    });
                    if (data.options.length === 0) {
                        $('#dynamic-fields').append(`
                            <div class="field-group multiple_add_group">
                                <div class="multiple_add">
                                    <input type="text" name="attribute_options[]" 
                                        class="attribute_options form-control" 
                                        placeholder="Enter Option values for Attribute">
                                </div>
                                <div class="multiple_add_remove">
                                    <button type="button" class="btn btn-danger remove-field">Remove</button>
                                </div>
                                <hr>
                            </div>
                        `);
                    }

            });
        });
    // Remove attribute option field
    $("#dynamic-fields").on("click", ".remove-field", function () {
        const fieldCount = $('#dynamic-fields .field-group').length;
         const $fieldGroup = $(this).closest(".multiple_add_group");
         const recordId = $('#recordId').val();
         
          
        if (fieldCount == 1) {
            $('#error-message1')
                .text(`No More Fields to remove.`)
                .fadeIn()
                .attr('tabindex', '-1')
                .focus()
                .delay(5000)
                .fadeOut();
        }
        if (recordId && recordId !== "new" && (fieldCount!=1)) {
            const optId = $(this).attr('id');
                // AJAX request to delete the record
                $.ajax({
                    url: `/delete_attr_option/${optId}`,
                    type: 'DELETE',
                     headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                   // data: { _token: '{{ csrf_token() }}' },
                    beforeSend: function () {
                        $(".remove-field").prop("disabled", true);
                    },
                    success: function (response) {
                        if (response.message) {
                            $fieldGroup.remove();
                            $('#success-message1').text(response.message) .fadeIn()
                            .attr('tabindex', '-1')
                            .focus()
                            .delay(5000)
                            .fadeOut();
                        } else {
                            $('#error-message1')
                            .text(`Failed to delete the record.`)
                            .fadeIn()
                            .attr('tabindex', '-1')
                            .focus()
                            .delay(5000)
                            .fadeOut();
                            return false;
                        }
                    },
                    error: function () {
                        $('#error-message1')
                            .text(`Error occurred while deleting the record.`)
                            .fadeIn()
                            .attr('tabindex', '-1')
                            .focus()
                            .delay(5000)
                            .fadeOut();
                            return false;
                    },
                    complete: function () {
                        $(".remove-field").prop("disabled", false);
                    },
                });
            } else {
                $fieldGroup.remove(); 
            }
    });


        $(document).on('click', '.delete-btn', function () {
            const itemId = $(this).data('id'); // Get item ID from the button
            const url = '/attribute_destroy/' + itemId ;
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
    