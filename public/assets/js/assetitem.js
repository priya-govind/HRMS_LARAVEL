
        $(document).ready(function () {
     const CategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'), {
                backdrop: 'static',
                keyboard: false
            });
    const BrandModal = new bootstrap.Modal(document.getElementById('addBrandModal'), {
                backdrop: 'static',
                keyboard: false
            });
     
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#item_type').change(function(){
            const selectedval=$(this).val();
                $.get('/assets_manage/' + selectedval+'/loadcatdata', function (response) {
                        if (response.load_data && response.load_data.length > 0) {
                            let options = "<option value=''>Select New Item</option>";
                            $.each(response.load_data, function(index, item){
                            options += "<option value= '"+item.id+"'>"+item.category_name+" </option>";
                            });
                             options += "<option value= 'others'>Others </option>";
                            $('#item_category').html(options);
                        }
                    if(selectedval=='licenses'){
                        $('#expiry_dt_col').show();
                    }
                });
            
        }); 
        $('#item_category').change(function(){
            if($(this).val() === 'others'){
                // Pass current item_type into modal hidden field
                $('#modal_item_type').val($('#item_type').val());
            CategoryModal.show();
            }
        });
        $('#item_brand').change(function(){
            if($(this).val() === 'others'){
            BrandModal.show();
            }
        });
        $('#AddCategoryForm').submit(function(e){
            e.preventDefault();
            const formData = $(this).serialize();

            $.post('/assets_manage/addCategory', formData, function(response){
                if(response.success){
                    // Close modal
                    CategoryModal.hide();
                    // Reload categories for current item_type
                    const selectedType = $('#item_type').val();
                    $.get('/assets_manage/' + selectedType + '/loadcatdata', function (res) {
                        if (res.load_data && res.load_data.length > 0) {
                            let options = "<option value=''>Select New Item</option>";
                            $.each(res.load_data, function(index, item){
                                options += "<option value='"+item.id+"'>"+item.category_name+"</option>";
                            });
                            options += "<option value='others'>Others</option>";
                            $('#item_category').html(options);

                            // Auto-select the newly added category
                            $('#item_category').val(response.new_id);

                        }
                    });
                } else {
                    alert('Error: ' + response.message);
                }
            });
        });
        $('#AddBrandForm').submit(function(e){
                    e.preventDefault();
                    const formData = $(this).serialize();

                    $.post('/assets_manage/addBrand', formData, function(response){
                        if(response.success){
                            // Close modal
                            BrandModal.hide();
                            // Reload categories for current item_type
                            const selectedType = $('#item_type').val();
                            $.get('/assets_manage/' + selectedType + '/loadbranddata', function (res) {
                                if (res.load_data && res.load_data.length > 0) {
                                    let options = "<option value=''>Select New Brand</option>";
                                    $.each(res.load_data, function(index, item){
                                        options += "<option value='"+item.id+"'>"+item.brand_name+"</option>";
                                    });
                                    options += "<option value='others'>Others</option>";
                                    $('#item_brand').html(options);

                                    // Auto-select the newly added category
                                    $('#item_brand').val(response.new_id);

                                }
                            });
                        } else {
                            alert('Error: ' + response.message);
                        }
                    });
                });
  $("#purchased_date,#expiry_date").datepicker({
    dateFormat: "dd-mm-yy",
    onSelect: function (selectedDate) {
      var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate);
      var minDate = new Date(dateObj);
      minDate.setDate(minDate.getDate() + 1);
      $("#endDate").datepicker("option", "minDate", minDate);
     // $("#endDate").datepicker("show"); // Show endDate picker after selecting startDate
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
            $('#AssetForm').trigger('reset');
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
                var validator = $("#AssetForm").validate();

                // Clear all errors and reset validation state
                validator.resetForm();

                // Also remove error classes from inputs
                $("#AssetForm").find(".error").removeClass("error");
                $("#AssetForm").find(".is-invalid").removeClass("is-invalid");
               // $("#AssetForm").find("[aria-invalid='true']").removeAttr("aria-invalid");


            // Fetch data for the selected record
            $.get('/assets_manage/' + id + '/edit', function (data) {
                $('#AssetForm').trigger('reset');
                $('#dataModalLabel').text('Edit Item Details');
                $('#edit_inventory').show();
                $('#recordId').val(data.id);
                $('#item_type').val(data.item_type);

                $.get('/assets_manage/' + data.item_type+'/loadcatdata', function (response) {
                        if (response.load_data && response.load_data.length > 0) {
                            let options = "<option value=''>Select New Item</option>";
                            $.each(response.load_data, function(index, item){
                            options += "<option value= '"+item.id+"'>"+item.category_name+" </option>";
                            });
                             options += "<option value= 'others'>Others </option>";
                            $('#item_category').html(options);
                        }
                    if(data.item_type=='licenses'){
                        $('#expiry_dt_col').show();
                    }
                     $('#item_category').val(data.item_category);
                });
                
               
                $('#item_brand').val(data.item_brand);
                $('#item_name').val(data.item_name);
                $('#serial_number').val(data.serial_number);
                $('#purchased_amount').val(data.purchased_amount);
                if(data.purchased_date!=''){
                    let dateStr = data.purchased_date; 
                    let parts = dateStr.split("-");    

                    let formatted = parts[2] + "-" + parts[1] + "-" + parts[0]; 
                
                    $('#purchased_date').val(formatted);
                }
                
                $('#asset_status').val(data.status);
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
            $('#AssetAssignForm').trigger('reset');
            // Fetch data for the selected record
            $.get('/assets_manage/' + id , function (data) {
                $('#assignInventoryModalLabel').text('Assign Inventory to User');
                $('#recordId_assign').val(data.data.id);
                $('#item_name_assign').val(data.data.item_name);
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
$.validator.addMethod("dateDMY", function(value, element) {
    return this.optional(element) || /^\d{2}-\d{2}-\d{4}$/.test(value);
}, "Enter a valid date (dd-mm-yyyy).");

        // Initialize validation
        $("#AssetForm").validate({
            errorClass: "is-invalid", // Add Bootstrap's 'is-invalid' class to highlight errors
            rules: {
                item_type: {
                    required: true,
                },
                item_category: {
                    required: true,
                },
                item_brand:{
                    required: function(element) {
                        return ($("#item_type").val() != "licenses");
                    }
                },
                item_name: {
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
                },
                purchased_amount: {
                    required: false,
                    number: true
                },
                purchased_date: {
                    required: false,
                    dateDMY: true // or custom regex if you want dd-mm-yyyy
                },
                expiry_date: {
                    required: false,
                    dateDMY: true
                }
            },
            messages: {
                item_type: {
                    required: "Select the Item Type.",
                   
                },
                item_category: {
                    required: "Select the Item Category.",
                   
                },
                item_brand: {
                    required: "Select the Brand Type",
                },
                 item_name: {
                    required: "Enter the Item Name",
                },
                 serial_number: {
                    required: "Enter the Serial Number.",
                },
                 new_assert: {
                    required: "Please Assign a new available item when damaged."
                },
                purchased_amount: { number: "Enter a valid number." },
                purchased_date: { dateDMY: "Enter a valid date (dd-mm-yyyy)." },
                expiry_date: { dateDMY: "Enter a valid date (dd-mm-yyyy)." }
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
                const url = $('#recordId').val() ? '/assets_manage/' + $('#recordId').val() 
                                                : 'assets_manage/'; 
                let formData = $('#AssetForm').serialize(); // collects all fields with name attributes

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
                        const url = 'assets_manage/store_assigned'; 
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
            const url = '/assets_manage/' + itemId;
            const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    myModal.show();
            // Add click listener for the Delete button inside the modal
            $('#confirmDelete').off('click').on('click', function () {
               
                $.get('/assets_manage/' + itemId + '/check_assigned', function (response) {
                    if(response.proceed==true){
                         const DatatableId='#AssetsTable';
                            deleteItem(itemId,url,DatatableId); 
                            myModal.hide();
                    } else {
                        const modalElement = document.getElementById('messageModal');
                        const myModal1 = new bootstrap.Modal(modalElement);
                        $('#messageModal .modal-body').text(response.message);
                        myModal1.show();
                         myModal.hide();
                    }
                });
               
            });
        });
        $('#asset_status').change(function(){
            const selectedval=$(this).val();
            const damagedId=$('#recordId').val();
            if(selectedval=='damaged' || selectedval=='retired'){
                $.get('/assets_manage/' + damagedId + '/replace_inventory', function (response) {
                    if(response.assigned==true){
                        $('#new_item').show();
                        if (response.available_items && response.available_items.length > 0) {
                            let options = "<option value=''>Select New Item</option>";
                            $.each(response.available_items, function(index, item){
                            options += "<option value= '"+item.id+"'>"+item.brand_name+" -  "+item.item_name+" - ("+item.serial_number+") </option>";
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