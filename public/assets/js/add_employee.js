    $(document).ready(function () {
       // $("form").validate(); // Initialize validation
      //  $("form").valid();
      $.validator.addMethod("pattern", function (value, element, regex) {
        return this.optional(element) || regex.test(value);
    }, "Invalid input.");
    $("input").on("keyup", function() {
    $(this).valid(); // Validate the field on every key press
});

        // Initialize validation
        var validator = $("#myForm").validate({
          rules: {
            name: {
                required: true,
                pattern: /^[a-zA-Z\s]+$/, // Allow only alphabets and spaces
            },
             password: {
                required: true,
                minlength: 6 // example: at least 6 characters
            },
            confirm_password: {
                required: true,
                equalTo: "#password" // must match the password field
            },
            emp_status: {
                required: true
            },
            "role_id[]": {
                required: true, // Ensures at least one checkbox is checked
            },
        },
        messages: {
            name: {
                required: "Please enter your name.",
                pattern: "Only alphabets and spaces are allowed.",
            },
            password: {
                required: "Please enter a password.",
                minlength: "Password must be at least 6 characters long."
            },
            confirm_password: {
                required: "Please confirm your password.",
                equalTo: "Passwords do not match."
            },
            emp_status: {
                required: "Please select an employee status."
            },
             "role_id[]": {
                required: "Please select at least one role.",
            },
        },
            errorPlacement: function (error, element) {
             if (element.attr("name") === "emp_status") {
                $("#emp_status-error").html(error).show();
             } else if (element.attr("name") === "role_id[]") {
                    $('.invalid-feedback').text(error.text()).show();
                } else {
                        error.insertAfter(element); // Default placement for other fields
                    }
            },
            highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid"); // Highlight error field
        },
        unhighlight: function (element) {
            $(element).addClass("is-valid").removeClass("is-invalid"); // Highlight valid field
        },
         submitHandler: function(form) {
                form.submit(); // submit only when everything is valid
            }
        });
        // Form Submission Validation (my update integrated here)
        $("#myForm").submit(function (e) {
            let isValid = true;
            // Stop form submission if validation fails
            if (!isValid) {
                e.preventDefault();
            }
        });
    });