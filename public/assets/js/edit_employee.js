$(document).ready(function () {
       // $("form").validate(); // Initialize validation
       // $("form").valid();
        $.validator.addMethod("pattern", function (value, element, regex) {
            return this.optional(element) || regex.test(value);
        }, "Invalid input.");

        $("input").on("keyup", function () {
            $(this).valid();
        });
    // Initialize validation
    var validator = $("#myForm").validate({
    rules: {
        name: {
            required: true,
            pattern: /^[a-zA-Z\s]+$/, // Allow only alphabets and spaces
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
        "role_id[]": {
            required: "Please select at least one role.",
        },
    },
    errorPlacement: function (error, element) {
        if (element.attr("name") === "emp_status") {
            error.appendTo($("#emp_status-error"));
        }else if (element.attr("name") === "role_id[]") {
                    $('.invalid-feedback').text(error.text()).show();
        }else {
            error.insertAfter(element);
        }
    },
    highlight: function (element) {
        $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element) {
        $(element).addClass("is-valid").removeClass("is-invalid");
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