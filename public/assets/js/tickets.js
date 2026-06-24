$('#ticket_type_id').change(function() {
          $('#problem_type_id').attr('style','pointer-events:visible;');
              var type = $(this).val();

              if(type) {
                  $.ajax({
                      url: '/get_problem_type/' + type,
                      type: 'GET',
                      success: function(data) {
                          $('#problem_type_id').empty();
                          $('#problem_type_id').append('<option value="">Select Problem Type</option>');
                          $.each(data, function(id, name) {
                              $('#problem_type_id').append('<option value="' + id + '">' + name + '</option>');
                          });
                           
                      }
                  });
              } else {
                  $('#problem_type_id').empty();
                  $('#problem_type_id').append('<option value="">Select Problem Type</option>');
              }
          });
          $('#addButton').click(function () {
            const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
            $('#dataModalLabel').text('Raise New Ticket');
            $('#dataForm').trigger('reset');
            $('#recordId').val('');
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
                    problem_type_id:{
                         required: true, 
                    },
                    ticket_name: {
                        required: true,
                        pattern: "^[a-zA-Z0-9\\s]*",
                    },
                },
                messages: {
                    ticket_type_id:{
                        required: "Please select a Ticket Type.",
                    },
                     problem_type_id:{
                        required: "Please select a Problem Type.",
                    },
                    ticket_name: {
                        required: "Ticket name is required.",
                        pattern: "Special characters are not allowed in the Problem Type name.",
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
                     const selectedType = $('#ticket_type_id').val();
                        if (selectedType == '6') {
                            $('#problem_type_id').rules('remove'); // Remove validation
                        } else {
                            $('#problem_type_id').rules('add', {
                                required: true,
                                messages: {
                                    required: "Please select a Problem Type."
                                }
                            });
                        }
                    const isEdit = $('#recordId').val();
                    const url = isEdit ? '/tickets/' + $('#recordId').val() : '/tickets';
                    const method = 'POST'; // Always POST, use _method for PUT

                    const formData = new FormData(form);
                    if (isEdit) {
                        formData.append('_method', 'PUT');
                    }         
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
                                  var table = $('#ticketTable').DataTable();
                                 table.ajax.reload(null, false);
                               $('#dataForm').trigger('reset');
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

             $("#AssignForm").validate({
                errorClass: "is-invalid", // Add Bootstrap's 'is-invalid' class to highlight errors
                rules: {
                    'assign_mem_id[]':{
                         required: true, 
                    },
                },
                messages: {
                    'assign_mem_id[]':{
                        required: "Select Members to Assign",
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
                    const isEdit = $('#hid_id').val();
                    const url = '/tickets/' + $('#hid_id').val()+'/assign' ;
                    const method = 'POST'; // Always POST, use _method for PUT
                    const myModal = bootstrap.Modal.getInstance(document.getElementById('assignModal'));
                    const formData = new FormData(form);
                    
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
                            $('#success-message').text(response.success).fadeIn().delay(5000).fadeOut();
                            setTimeout(function () {
                                myModal.hide();
                                  var table = $('#ticketTable').DataTable();
                                 table.ajax.reload(null, false);
                               $('#AssignForm').trigger('reset');
                              
                            }, 2000);
                        },
                        error: function (xhr) {
                            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                            $('#error-message').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
                        },
                        complete: function () {
                            $('#saveBtn').prop('disabled', false).text('Save');
                        },
                    });
                },
            });
        });
        $('#ticket_type_id').on('change', function () {
            const selectedType = $(this).val();

            if (selectedType == '6') {
                $('#problem_type_id').closest('.mb-3').hide(); // Hide the field
                $('#problem_type_id').val(''); // Clear value
            } else {
                $('#problem_type_id').closest('.mb-3').show(); // Show the field
            }
        });

        $(document).off('click', '.editButton').on('click', '.editButton', function () {
            const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
            const id = $(this).data('id');
            $('#dataModalLabel').text('View Ticket Information');
            // Fetch data for the selected record
            $.get('/tickets/' + id + '/edit', function (data) {
                $('#recordId').val(data.ticket.id);
                $('#ticket_type_id_edit').val(data.ticket.ticket_type_id || '');
                if(data.ticket_type_id=='6'){
                   $('#problem_type_id_edit').closest('.mb-3').hide();
                } else {
                    $('#problem_type_id_edit').closest('.mb-3').show();
                }
                $('#problem_type_id_edit').val(data.ticket.problem_type_id || '');
                $('#ticket_name_edit').val(data.ticket.ticket_name);
                $('#ticket_desc_edit').val(data.ticket.ticket_desc);
                if(data.ticket.ticket_status==11){
                    $('#reply_to').show();
                    $('#ticket_reply_edit').val(data.solved_by_reply_to)
                }
                $('#viewForm').find('input, select, textarea').prop('disabled', true);
                    if (data.assigned_members && data.assigned_members.length > 0) {
                         $('.assignedMember').show();
                    let memberList = '';
                    data.assigned_members.forEach(function (member) {
                        memberList += `<li>${member.name}</li>`;
                    });
                    $('#assignedMembersList').html(memberList);
                    } else {
                        $('.assignedMember').hide();
                    }
            });
        });
        $(document).off('click', '#AssignTicket').on('click', '#AssignTicket', function () {
             const myModal = new bootstrap.Modal(document.getElementById('assignModal'), {
                backdrop: 'static',
                keyboard: false
            });
            const id = $(this).data('id');
            $('#assignModalLabel').text('Assign Ticket');
            $('#hid_id').val(id);
            $('#ownerId').val($(this).data('owner'));
                $.get('/tickets/' + id + '/assigned_members', function (data) {
                  const assignedIds = data.map(item => item.assign_mem_id);
                    $('#assign_mem_id').val(assignedIds).trigger('change');
                     $('#assign_comments').val(data[0].assign_comments); 
                });
                myModal.show();
        });
        $(document).off('click', '#UpdateTicket').on('click', '#UpdateTicket', function () {
             const myModal = new bootstrap.Modal(document.getElementById('UpdateModal'), {
                backdrop: 'static',
                keyboard: false
            });
            const id = $(this).data('id');
            $('#UpdateModalLabel').text('Update Ticket Status');
                $.get('/tickets/' + id + '/update_status', function (data) {
                        $('#ticket_id').val(id);
                       $('#ticket_name_update').val(data.ticket.ticket_name);
                       console.log(data.assign_members[0].mem_comment);
                       $('#reply_to_update').val(data.assign_members[0].mem_comment);
                       $('#ticket_status').val(data.ticket.ticket_status);
                       if(data.ticket.ticket_status==11){
                        $("#UpdateTicketForm :input").not("#reply_to_update,#ticket_name_update,#ticket_id, :button, [type=submit]").prop("disabled", true);
                        //not("button, input[type=button],#reply_to")
                       }
                    //     else {
                    //     $("#UpdateTicketForm :input").prop("disabled", false);
                    //    }
                });
              
                myModal.show();
        });
         $("#UpdateTicketForm").validate({
        errorClass: "is-invalid", 
        rules: {
            ticket_status: {
                required: true,
            },
            reply_to:{
                required :true,
            }
        },
        messages: {
            ticket_status: {
                required: "Task Status is required.",
            },
            reply_to:{
                required :"Add your Comments",
            }
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
            const url = '/ticket_ind_update';
            const method = 'PUT';
            const myModal = bootstrap.Modal.getInstance(document.getElementById('UpdateModal'));
            // Submit the form via AJAX
            $.ajax({
                url: url,
                type: method,
                data: formData,
                beforeSend: function () {
                    $('#updateBtn').prop('disabled', true).text('Saving...');
                },
                success: function (response) {
                    $('#success-message1').text(response.message).fadeIn().delay(5000).fadeOut();
                   
                    // Delay page reload
                    setTimeout(function () {
                        myModal.hide();
                         var table = $('#ticketTable').DataTable();
                       table.ajax.reload(null, false);
                       
                    }, 500); // Delay by 500ms to allow the modal to close
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    $('#error-message1').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
                },
                complete: function () {
                    $('#updateBtn').prop('disabled', false).text('Save');
                },
            });
        },
    });
        
        
