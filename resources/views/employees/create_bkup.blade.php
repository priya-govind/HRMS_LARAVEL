@extends('/layouts.app')
@section('content')
      
      <!-- partial:partials/_navbar.html -->
      @include('/layouts.includes.topbar')
     
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
   
        <!-- partial:partials/_sidebar.html -->
        @include('/layouts.includes.sidebar')
        
        <!-- partial -->
        <div class="main-panel">
          
          <div class="content-wrapper">
            
          <div class="col-md-8 grid-margin stretch-card">
            
                <div class="card">
                  
                  <div class="card-body">
                  <nav class="p-1">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employees') }}">Employeees</a></li>
                <li class="breadcrumb-item active">Create Employee</li>
            </ol>
        </nav>
                  <div class="float-end">
                    <a href="{{ route('employees') }}" class="btn btn-primary btn-sm">&larr; Back</a><br/>
                </div>
         
                    <h4 class="card-title">Employee Details</h4>
               
                    @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                    </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('success') }}
                        </div>
                    @endif
                    <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                    <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                    <form class="myForm" id="myForm" onSubmit=" return fncall();"  action="{{route('employees.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="exampleInputName1">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Name">
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail3">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputPassword4">Password</label>
                        <input type="password" class="form-control"  id="password" name="password" placeholder="Password" value="{{ old('password') }}" required>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputPassword4">Re Type Password</label>
                        <input type="password" class="form-control"  id="confirm_password" name="confirm_password" placeholder="Password" value="{{ old('confirm_password') }}" required>
                      </div>
                      <div class="form-group gender-container">
                        <label for="exampleSelectGender">Gender</label>
                          <input type="radio" name="gender" value="male" required>Male
                          <input type="radio" name="gender" value="female" required>Female
                      </div>
                      <br/><br/><div class="form-group">
                        <label>Image upload</label>
                        <input type="file" name="image" required>
                 
                      </div>
                      <div class="form-group">
                        <label for="exampleTextarea1">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="4" value="{{ old('address') }}" required></textarea>
                      </div>
                      <div class="form-group">
                        <label for="exampleTextarea1">Select Roles</label>
                        <div class="checkbox-container">
                            @foreach ($roles as $role)
                            <label class="checkbox-item">
                                <input type="checkbox" class="roleCheckbox" name="role_id[]" value="{{ $role->id }}"> {{ $role->role_name }}
                            </label>
                            @endforeach
                            <div class="invalid-feedback">
                                Please select at least one role.
                            </div>

                    </div>
                      </div>
                      <div class="form-group">
                      <label for="exampleTextarea1">Upload Documents</label><br/>
                      <div id="dynamic-fields">
                        <div class="field-group">
                            <input type="file" name="doc_images[]">
                            <input type="text" name="emp_qualification[]" placeholder="Enter Qualification">
                            <input type="text" name="emp_marks[]" placeholder="Enter Marks">
                            <button type="button" class="remove-field">Remove</button>
                        </div><hr>
                    </div>
                   <button type="button" id="add-field">Add More Documents</button>
                   <div class="invalid-feedback"></div>
                        
                      </div>
                      <button type="submit" class="btn btn-gradient-primary me-2">Submit</button>
                      
                    </form>
                  </div>
                </div>
              </div>
         
           
          </div>
        
<script>
  function fncall() {
        const checkboxes = document.querySelectorAll('.roleCheckbox');
        const isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

        if (!isChecked) {
          // $("#error-message1").text(`Please select at least one role. #${index + 1}`).fadeIn().delay(5000).fadeOut();
          // $('#error-message1')
          //           .text(`Please select at least one role.`)
          //           .fadeIn()
          //           .attr('tabindex', '-1')
          //           .focus()
          //           .delay(5000)
          //           .fadeOut(); 
           // alert('Please select at least one role.');
            return false; // Stop form submission
        }
    const fileInputs = document.querySelectorAll('input[name="doc_images[]"]');
    const qualifyInputs = document.querySelectorAll('input[name="emp_qualification[]"]');
    const marksInputs = document.querySelectorAll('input[name="emp_marks[]"]');

    let isValid = true;

    // Validate dynamic fields for document uploads
    fileInputs.forEach((input, index) => {
        if (!input.value) {
            isValid = false;
            fileInput.addClass("is-invalid");
            fileInput.after(`<label class="error">Please upload a file for document.</label>`);
        } else {
            fileInput.removeClass("is-invalid");
            fileInput.siblings(".error").remove(); // Remove error label
        }

    });

    if (!isValid) {
        return false; // Stop form submission if validation fails
    }

    // Validate dynamic fields for employee qualifications
    qualifyInputs.forEach((input, index) => {
        if (!input.value) {
            isValid = false;
           // $("#error-message1").text(`Please enter qualification for employee #${index + 1}`).fadeIn().delay(5000).fadeOut();
            $('#error-message1')
                    .text(`Please enter qualification for employee #${index + 1}`)
                    .fadeIn()
                    .attr('tabindex', '-1')
                    .focus()
                    .delay(5000)
                    .fadeOut();
            return false; // Stop iteration on first error
        }
    });

    if (!isValid) {
        return false; // Stop form submission if validation fails
    }

    // Validate dynamic fields for employee marks
    marksInputs.forEach((input, index) => {
        if (!input.value || isNaN(input.value)) { // Check if marks are entered and valid numbers
            isValid = false;
            $("#error-message1").text(`Please enter valid marks for employee #${index + 1}`).fadeIn().delay(5000).fadeOut();
            return false; // Stop iteration on first error
        }
    });

    if (!isValid) {
        return false; // Stop form submission if validation fails
    }

    // If everything is valid, allow form submission
    return true;
}

    $(document).ready(function () {
      $("#myForm").validate({
        rules: {
            name: {
                required: true,
               
            },
            email: {
                required: true,
                email: true
            },
            password: {
                required: true
            },
            confirm_password: {
                required: true,
                equalTo: "#password"
            },
            gender: {
                required: true
            },
            image: {
                required: true
            },
            address: {
                required: true
            },
            "role_id[]": {
                required: true
            },
        },
        messages: {
            name: "Please Enter Name.",
            email: "Please enter a valid email address.",
            password: "Please enter a password.",
            confirm_password: "Passwords must match.",
            gender: "Please select a gender.",
            image: "Please upload an image.",
            address: "Please provide your address.",
            "role_id[]": "Please select at least one role.",
        },
        errorPlacement: function (error, element) {
          if (element.attr("name") === "gender") {
            // Place the error message below the radio button group
            error.insertAfter(".gender-container"); // Ensure .gender-container wraps your radio buttons
        } else if (element.attr("name") === "role_id[]") {
                error.insertAfter(".checkbox-container");
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element) {
          if (element.type === "checkbox" || element.type === "radio") {
              $(element).closest('.form-group').addClass('is-invalid');
              $(element).closest('div').addClass('invalid_chkbox');

          } else {
              $(element).addClass('is-invalid');
          }

        },
        unhighlight: function (element) {
          if (element.type === "checkbox" || element.type === "radio") {
              $(element).closest('.form-group').removeClass('is-invalid');
              $(element).closest('div').removeClass('invalid_chkbox');
          } else {
              $(element).removeClass('is-invalid');
          }

        },
        submitHandler: function (form) {
            // Custom validation for dynamic document fields
            let isValid = true;



            if (!isValid) {
                return false; // Prevent form submission if validation fails
            }

            // Show success message and submit the form
            $("#success-message1").text("Form submitted successfully!").fadeIn().delay(5000).fadeOut();
            form.submit();
        }
    });

    // Add dynamic fields functionality
// Ensure only one click event is bound to the "Add" button
$("#add-field").off("click").on("click", function () {
  const fieldCount = $('#dynamic-fields .field-group').length;

if (fieldCount < 5) {
  const newFieldGroup = `
        <div class="field-group">
            <input type="file" name="doc_images[]" class="doc-image" required>
            <input type="text" name="emp_qualification[]" class="emp-qualification" placeholder="Enter Qualification" required>
            <input type="text" name="emp_marks[]" class="emp-marks" placeholder="Enter Marks" required>
            <button type="button" class="remove-field">Remove</button><hr>
        </div>
    `;

    // Append the new fields to the DOM
    $("#dynamic-fields").append(newFieldGroup);
} else {
  alert('You can add a maximum of 5 fields.');
}
// Define validation rules for all fields in a single configuration object
const validationRules = {
    ".doc-image": {
        rules: {
            required: true
        },
        messages: {
            required: "Please upload a document."
        }
    },
    ".emp-qualification": {
        rules: {
            required: true
        },
        messages: {
            required: "Please enter a qualification."
        }
    },
    ".emp-marks": {
        rules: {
            required: true,
            number: true
        },
        messages: {
            required: "Please enter marks.",
            number: "Please enter valid numeric marks."
        }
    }
};

// Apply validation rules to all field groups dynamically
function applyValidation() {
    $("#dynamic-fields .field-group").each(function() {
        Object.keys(validationRules).forEach(function(selector) {
            const field = $(this).find(selector);
            if (field.length) {
                const rulesConfig = validationRules[selector];
                field.rules("add", rulesConfig.rules, rulesConfig.messages);
            }
        }.bind(this));
    });
}

// Initial validation setup for existing rows
applyValidation();

// Trigger validation setup on adding a new row
$("#add-row-button").on("click", function() {
    // Add your row creation logic here...
    applyValidation(); // Reapply validation to include the new row
});
});


    $("#dynamic-fields").on("click", ".remove-field", function () {
      const fieldCount = $('#dynamic-fields .field-group').length;
      if(fieldCount!=1){
        $(this).closest(".field-group").remove();
      } else {
        alert('No More Fields to remove');
      }
    });
   
        // $(document).on('click', '.remove-field', function () {
        //     $(this).closest('.field-group').remove();
        // });
    });
</script>
          @endsection
         