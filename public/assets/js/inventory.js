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
            $('#dataModalLabel').text('Add New Inventory Details');
            $('#InventoryForm').trigger('reset');
             $('#new_item').hide();
            $('#recordId').val('');
        });

        $(document).off('click', '.editButton').on('click', '.editButton', function () {
            const myModal = new bootstrap.Modal(document.getElementById('dataModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
            const id = $(this).data('id');
                // Get the validator instance
                var validator = $("#InventoryForm").validate();

                // Clear all errors and reset validation state
                validator.resetForm();

                // Also remove error classes from inputs
                $("#InventoryForm").find(".error").removeClass("error");
                $("#InventoryForm").find(".is-invalid").removeClass("is-invalid");
               // $("#InventoryForm").find("[aria-invalid='true']").removeAttr("aria-invalid");


            // Fetch data for the selected record
            $.get('/manage_inventory/' + id + '/edit', function (data) {
                $('#InventoryForm').trigger('reset');
                $('#dataModalLabel').text('Edit Inventory Details');
                $('#edit_inventory').show();
                $('#recordId').val(data.id);
                $('#asset_type').val(data.asset_type);
                $('#asset_brand').val(data.asset_brand);
                $('#asset_name').val(data.asset_name);
                $('#serial_number').val(data.serial_number);
                $('#asset_status').val(data.asset_status);
                 $('#new_item').hide();
            });
        });
        $(document).off('click', '.assignInventory').on('click', '.assignInventory', function () {
            const myModal = new bootstrap.Modal(document.getElementById('assignInventoryModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
            const id = $(this).data('id');

            // Fetch data for the selected record
            $.get('/manage_inventory/' + id + '/show', function (data) {
                $('#assignInventoryModalLabel').text('Assign Inventory to User');
                $('#recordId_assign').val(data.data.id);
                $('#asset_name_assign').val(data.data.asset_name);
                $('#serial_number_assign').val(data.data.serial_number);
                if(data.user!=''){
                    $('#user_id').val(data.user);
                }
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
        $("#InventoryForm").validate({
            errorClass: "is-invalid", // Add Bootstrap's 'is-invalid' class to highlight errors
            rules: {
                asset_type: {
                    required: true,
                },
                asset_brand: {
                    required: true,
                },
                asset_name: {
                    required: true,
                    pattern: "^[a-zA-Z0-9\\s]*",
                },
                 serial_number: {
                    required: true,
                },
                 new_assert: {
                    required: function(element) {
                        return ($("#asset_status").val() === "damaged" || $("#asset_status").val() === "retired");
                    }
                }
            },
            messages: {
                asset_type: {
                    required: "Select the Item Type.",
                   
                },
                asset_brand: {
                    required: "Select the Brand Type",
                },
                 asset_name: {
                    required: "Enter the Item Name",
                },
                 serial_number: {
                    required: "Enter the Serial Number.",
                },
                 new_assert: {
                    required: "Please Assign a new available item when damaged."
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
                const url = $('#recordId').val() ? '/manage_inventory/' + $('#recordId').val() 
                                                : 'manage_inventory/'; 
                let formData = $('#InventoryForm').serialize(); // collects all fields with name attributes

                // If this is an update, append _method=PUT for Laravel to handle it
                if ($('#recordId').val()) {
                formData += '&_method=PUT';
                }
                const myModal = bootstrap.Modal.getInstance(document.getElementById('dataModal'));
                const success='#success-message1';
                const fail='#error-message1';
                const DatatableId='#AssetsTable';
                callajax_noreturn(url,'POST',formData,success,fail,myModal,DatatableId);
            },
        });

        $("#AssetAssignForm").validate({
                    errorClass: "is-invalid", // Add Bootstrap's 'is-invalid' class to highlight errors
                    rules: {
                        user_id: {
                            required: true,
                        },
                    },
                    messages: {
                        user_id: {
                            required: "Select the User.",
                        
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
                        const id = $('#recordId_assign').val();
                        const method = 'POST';
                        const url = 'manage_inventory/store_assigned'; 
                        let formData = $('#AssetAssignForm').serialize(); // collects all fields with name attributes

                        const myModal = bootstrap.Modal.getInstance(document.getElementById('assignInventoryModal'));
                        const success='#success-message2';
                        const fail='#error-message2';
                        const DatatableId='#AssetsTable';
                        callajax_noreturn(url,'POST',formData,success,fail,myModal,DatatableId);
                    },
        });



        $(document).on('click', '.delete-btn', function () {
            const itemId = $(this).data('id'); // Get item ID from the button
            const url = '/manage_inventory/' + itemId;
            const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    myModal.show();
            // Add click listener for the Delete button inside the modal
            $('#confirmDelete').off('click').on('click', function () {
                const DatatableId='#AssetsTable';
                deleteItem(itemId,url,DatatableId); 
                myModal.hide();
            });
        });
        $('#asset_status').change(function(){
            const selectedval=$(this).val();
            const damagedId=$('#recordId').val();
            if(selectedval=='damaged' || selectedval=='retired'){
                $.get('/manage_inventory/' + damagedId + '/replace_inventory', function (response) {
                    if(response.assigned==true){
                        $('#new_item').show();
                        if (response.available_items && response.available_items.length > 0) {
                            let options = "<option value=''>Select New Item</option>";
                            $.each(response.available_items, function(index, item){
                            options += "<option value= '"+item.id+"'>"+item.brand_name+"  "+item.asset_name+" - ("+item.serial_number+") </option>";
                            });
                            $('#new_assert').html(options);
                        }
                    } else {
                         $('#new_item').hide();
                    }
                });
            }
        });        
    });
    