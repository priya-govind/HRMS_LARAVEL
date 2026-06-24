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
            
          <div class="col-md-12 grid-margin stretch-card">
            
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
                    <form class="myForm" id="myForm" action="{{ route('employees.update', $user['id']) }}"   method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                      <div class="form-group">
                        <label for="exampleInputName1">Name</label>
                        <input type="text" class="form-control" id="name" name="name" pattern="[a-zA-Z0-9\s]*" title="Special Characters not allowed"  value="{{ $user['name'] }}"  required  placeholder="Name">
                        
                      </div>
                      <div class="form-group">
                        <label for="exampleSelectGender">Employee Status: &nbsp;&nbsp;</label>
                          <input type="radio" name="emp_status" value="1" @if($user['emp_status'] == '1') checked @endif  required>Active &nbsp;&nbsp;&nbsp;
                          <input type="radio" name="emp_status" value="0" @if($user['emp_status'] == '0') checked @endif required>InActive
                        
                      </div>
                       <div id="emp_status-error" class="error"></div> 
                      <div class="form-group">
                        <label for="exampleInputEmail3">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ $user['email'] }}" required>
                      </div>
                      <div class="form-group">
                        <label for="exampleSelectGender">Gender</label>
                          <input type="radio" name="gender" value="male"  @if($user['gender'] == 'male') checked @endif required>Male
                          <input type="radio" name="gender" value="female"  @if($user['gender'] == 'female') checked @endif  required>Female
                        
                      </div>
                      <div id="gender-error" class="error"></div> 
                      <div class="form-group">
                        <label>Image upload</label>
                        <input type="file" name="image" @if($user['image'] == '') required @endif >
                        <img src="{{ url('images/' . $user['image']) }}" alt="image" style="width: 10%; height: 10%;">
                 
                      </div>
                      <div class="form-group">
                        <label for="exampleTextarea1">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="4" required> {{ $user['address'] }} </textarea>
                      </div>
                      <div class="form-group">
                        <label for="exampleTextarea1">Select Roles</label>
                        <div class="checkbox-container">
                            @foreach ($roles as $role)
                            <label class="checkbox-item">
                                <input type="checkbox" class="roleCheckbox" name="role_id[]" value="{{ $role->id }}" {{ in_array($role->id, $userRoles) ? 'checked' : '' }}> 
                                {{ $role->role_name }}
                            </label>
                            @endforeach
                            <div class="invalid-feedback">
                                Please select at least one role.
                            </div>
                    </div>
                      </div>
                    <div class="form-group">
                        <label for="exampleTextarea1">Select Team Type</label>
                        <select name="team_type" id="team_type">
                        <option value="">Select</option>   
                        @foreach ($team_types as $t_type)
                             <option @if($user['team_type']==$t_type->id ) selected @endif value="{{ $t_type->id }}"> {{ $t_type->team_typ_name }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                         <label for="emp_status">Access For IT Support: &nbsp;&nbsp;</label>
                            <input type="radio" name="support_access" id="support_access" value="1"  @if($user['support_access'] === 1) checked @endif  > Allow &nbsp;&nbsp;&nbsp;
                            <input type="radio" name="support_access" id="support_access" value="0"  @if($user['support_access'] !== 1) checked @endif> Don't Allow
                    </div>
                    
                     <div id="support_access-error" class="error" style="display:none;"></div>
                      <div id="success-message1" class="alert alert-success"  role="alert"  style="display: none;"></div>
                      <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                      <div class="form-group">
                      <label for="exampleTextarea1">Upload Documents</label><br/>
                      <div id="dynamic-fields">
                            <div class="field-group multiple_add_group">
                            <div class="multiple_add">
                                Certificate Image
                            </div>
                            <div class="multiple_add">
                                Qualification
                            </div>
                            <div class="multiple_add">
                                Marks
                            </div>
                            </div>
                        @foreach($documents as $docs)
                        <div class="field-group multiple_add_group" data-record-id="{{ $docs->id }}" id="dynamic-field-{{ $loop->index }}">
                            <div class="multiple_add">
                            <input type="hidden" name="old_doc_id[]" value="{{$docs->id}}">
                                @if(!empty($docs->image_path))
                                <input type="file" name="doc_images[]" style="display:none;">
                                <img src="{{ url('docs/' . $docs->image_path) }}"  class="doc_immg"  alt="image" style="width: 10%; height: 10%;">
                                <a class="del_doc_img text-black" id="{{$docs->id}}" style="text-decoration: none;  cursor: pointer;">X</a>
                                
                                @else  
                                <input type="file" name="doc_images[]">
                                @endif
                            </div>
                            <div class="multiple_add">
                                <input type="text" name="emp_qualification[]" value="{{ $docs->emp_qualification }}"  placeholder="Enter Qualification" readonly>
                            </div>
                            <div class="multiple_add">
                                <input type="text" name="emp_marks[]"  value="{{ $docs->emp_marks }}"  placeholder="Enter Marks" readonly>
                            </div>
                            <div class="multiple_add_remove">
                                <button type="button" class="remove-field">Remove</button>
                            </div>
                            <hr>
                        </div>
                        @endforeach

                    </div>
                   <button type="button" id="add-field">Add More Documents</button>

                        
                      </div>
                      <div id="success-message2" class="alert alert-success"  role="alert"  style="display: none;"></div>
                      <div id="error-message2" class="alert alert-danger" style="display: none;"></div>
                      <div class="form-group">
                      <label for="exampleTextarea1">Add Work Experience</label><br/>
                      <div id="dynamic-fields1">
                      <div class="field-group multiple_add_group">
                      <div class="multiple_add">
                       Company Name
                      </div>
                      <div class="multiple_add">
                        Years of Experience
                      </div>
                      </div>
                      @foreach($experience as $exp)
                     
                      <div class="field-group multiple_add_group" data-record-id="{{ $exp->id }}" id="dynamic-field-{{ $loop->index }}">
                      <input type="hidden" name="old_exp_id[]" value="{{$exp->id}}">
                          <div class="multiple_add">
                              <input type="text" name="company_nme[]"   value="{{ $exp->company_name }}"  placeholder="Enter Company Name" readonly>
                          </div>
                          <div class="multiple_add">
                              <input type="text" name="yr_experience[]"   value="{{ $exp->yr_experience }}"  placeholder="Years of Experience" readonly>
                          </div>
                          <div class="multiple_add_remove">
                              <button type="button" class="remove-field1">Remove</button>
                          </div>
                          <hr>
                      </div>
                      @endforeach

                    </div>
                   <button type="button" id="add-field1">Add More Experience</button>

                        
                      </div>
                      <div id="success-message3" class="alert alert-success"  role="alert"  style="display: none;"></div>
                      <div id="error-message3" class="alert alert-danger" style="display: none;"></div>
                      <div class="form-group">
                      <label for="exampleTextarea1">Add Certification Details</label><br/>
                      <div id="dynamic-fields2">
                      <div class="field-group multiple_add_group">
                      <div class="multiple_add">
                       Certification Name
                      </div>
                      <div class="multiple_add">
                        Certification File
                      </div>
                      </div>
                      @foreach($certificate as $cert)
                     
                      <div class="field-group multiple_add_group" data-record-id="{{ $cert->id }}" id="dynamic-field-{{ $loop->index }}">
                      <input type="hidden" name="old_cert_id[]" value="{{$cert->id}}">
                          <div class="multiple_add">
                              <input type="text" name="certification[]" value="{{ $cert->certification }}"  placeholder="Enter Certification" readonly>
                          </div>
                          <div class="multiple_add">
                            @if(!empty($cert->cer_image))
                          <input type="file" name="cer_image[]" style="display:none;">
                          <img src="{{ url('certificate/' . $cert->cer_image) }}" class="cert_immg"  alt="image" style="width: 10%; height: 10%;">
                          <a class="del_cert_img text-black" id="{{$cert->id}}" style="text-decoration: none;  cursor: pointer;">X</a>
                          @else 
                          <input type="file" name="cer_image[]">
                          @endif
                          </div>
                          <div class="multiple_add_remove">
                              <button type="button" class="remove-field2">Remove</button>
                          </div>
                          <hr>
                      </div>
                      @endforeach

                    </div>
                   <button type="button" id="add-field2">Add More Certifications</button>

                        
                      </div>
                      <button type="submit" class="btn btn-gradient-primary me-2">Submit</button>
                      
                    </form>
                  </div>
                </div>
              </div>
         
           
          </div>
          
          <script>
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
        team_type:{
            required:true,
        },
        "doc_images[]": {
            required: function (element) {
                console.log("Checking for image:", $(element).closest(".multiple_add").find("img.doc_immg").length);

                return $(element).closest(".multiple_add").find("img.doc_immg").length === 0;
            }
        },
        "emp_qualification[]": { required: true },
        "emp_marks[]": { required: true, number: true },
        "company_nme[]": { required: true },
        "yr_experience[]": { required: true, number: true },
        "certification[]": {
            required: function (element) {
                return $(element).closest(".multiple_add").find("img.cert_immg").length === 0;
            }
        },
        "cer_image[]": { 
            required: function (element) {
                console.log("Checking for image:", $(element).closest(".multiple_add").find("img.cert_immg").length);

                return $(element).closest(".multiple_add").find("img.cert_immg").length === 0;
            }
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
        team_type:{
            required:"Please Select Team Type.",
        },
        "doc_images[]": { required: "Please upload a document456." },
        "emp_qualification[]": { required: "Please enter the qualification." },
        "emp_marks[]": { required: "Please enter marks.", number: "Marks must be numeric." },
        "company_nme[]": { required: "Please enter company name." },
        "yr_experience[]": { required: "Please enter years of experience.", number: "Years must be numeric." },
        "certification[]": { required: "Please enter Certification Name" },
        "cer_image[]": { required: "Please upload Certification Image." },
    },
    errorPlacement: function (error, element) {
        if (element.attr("name") === "gender") {
            error.appendTo($("#gender-error"));
        } else if (element.attr("name") === "emp_status") {
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
         // Apply validation when file changes
    $('input[name="doc_images[]"]').on("change", function () {
        $(this).valid();
    });


    $("#add-field").click(function () {
    const fieldCount = $("#dynamic-fields .field-group").length;

    if (fieldCount < 5) {
        const newFieldGroup = `
            <div class="field-group multiple_add_group">
                <div class="multiple_add">
                    <input type="file" name="doc_images[]" class="doc-image">
                </div>
                <div class="multiple_add">
                    <input type="text" name="emp_qualification[]" class="emp-qualification" placeholder="Enter Qualification">
                </div>
                <div class="multiple_add">
                    <input type="text" name="emp_marks[]" class="emp-marks" placeholder="Enter Marks">
                </div>
                <div class="multiple_add_remove">
                    <button type="button" class="remove-field">Remove</button>
                </div>
                <hr>
            </div>`;

        $("#dynamic-fields").append(newFieldGroup);

        // // **Reinitialize validation on newly added elements**
        // console.log("Applying validation to:", $("#dynamic-fields .field-group").last().find(".doc-image"));
        // $("#dynamic-fields .field-group").each(function () {
        //     var $docImage = $(this).find(".doc-image");
            
        //     // Remove validation only if the field already has rules applied
        //     if ($docImage.hasClass("validated")) {
        //         $docImage.rules("remove");
        //     }
        // });

        // $("#dynamic-fields .field-group").last().find(".doc-image").addClass("validated").rules("add", {
        //     required: true,
        //     messages: { required: "Please upload a document." }
        // });



        $(".emp-qualification").last().rules("add", {
            required: true,
            messages: { required: "Please enter the qualification." }
        });

        $(".emp-marks").last().rules("add", {
            required: true,
            number: true,
            messages: { required: "Please enter marks.", number: "Marks must be numeric." }
        });

        // **Refresh the validation instance to ensure old fields are not revalidated**
        $("#myForm").validate().resetForm();
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
        $("#add-field1").click(function () {
                        const fieldCount = $("#dynamic-fields1 .field-group").length;
            
                        if (fieldCount < 5) { // Limit to 5 sets
                            const newFieldGroup = `
                                <div class="field-group multiple_add_group">
                                   <div class="multiple_add">
                                        <input type="text" name="company_nme[]" class="company-nme" placeholder="Enter Company Name">
                                    </div>
                                    <div class="multiple_add">
                                        <input type="text" name="yr_experience[]" class="yr-experience" placeholder="Enter Years of Experience">
                                    </div>
                                    <div class="multiple_add_remove">
                                        <button type="button" class="remove-field1">Remove</button>
                                    </div><hr>
                                </div>
                            `;
            
                            $("#dynamic-fields1").append(newFieldGroup);
            
                            // Apply validation to new fields
                            $(".company-nme").last().rules("add", {
                                required: true,
                                messages: {
                                    required: "Enter Company Name."
                                }
                            });
            
                            $(".yr-experience").last().rules("add", {
                                required: true,
                                number: true,
                                messages: {
                                    required: "Enter Years of Experience.",
                                    number: "Marks must be numeric."
                                }
                            });
                        } else {
                          $('#error-message2')
                                .text(`You can add a maximum of 5 fields.`)
                                .fadeIn()
                                .attr('tabindex', '-1')
                                .focus()
                                .delay(5000)
                                .fadeOut();
                        }
                    });

                      // Add dynamic fields
                      $("#add-field2").click(function () {
                          const fieldCount = $("#dynamic-fields2 .field-group").length;
              
                          if (fieldCount < 5) { // Limit to 5 sets
                              const newFieldGroup = `  <div class="field-group multiple_add_group">
                          <div class="multiple_add">
                              <input type="text" name="certification[]" class="certification" placeholder="Enter Certification">
                          </div>
                          <div class="multiple_add">
                          <input type="file" name="cer_image[]" class="cer-image">
                          </div>
                          <div class="multiple_add_remove">
                              <button type="button" class="remove-field2">Remove</button>
                          </div>
                          <hr>
                         </div>`;
              
                              $("#dynamic-fields2").append(newFieldGroup);
              
                              // Apply validation to new fields
                            //   $(".cer-image").last().rules("add", {
                            //       required: true,
                            //       messages: {
                            //           required: "Please upload certification image."
                            //       }
                            //   });
              
                              $(".certification").last().rules("add", {
                                  required: true,
                                  messages: {
                                      required: "Please enter certification name."
                                  }
                              });
                              $("#myForm").validate().resetForm();
                          } else {
                            $('#error-message3')
                                  .text(`You can add a maximum of 5 fields.`)
                                  .fadeIn()
                                  .attr('tabindex', '-1')
                                  .focus()
                                  .delay(5000)
                                  .fadeOut();
                          }
                      });

                      // Remove dynamic field group
        $("#dynamic-fields").on("click", ".remove-field", function () {
            
           const fieldCount = $('#dynamic-fields .field-group').length;
           const $fieldGroup = $(this).closest(".multiple_add_group");
            const recordId = $fieldGroup.data("record-id");

            // Validate fields in the group before deletion
            $fieldGroup.find("input").each(function () {
                $("#myForm").validate().element(this);
            });
            if((fieldCount==1)){
                $('#error-message1')
                    .text(`No More Fields to remove.`)
                    .fadeIn()
                    .attr('tabindex', '-1')
                    .focus()
                    .delay(5000)
                    .fadeOut();
                    return false;
            }
            if (recordId && recordId !== "new" && (fieldCount!=1)) {
                // AJAX request to delete the record
                $.ajax({
                    url: `/delete_document/${recordId}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    beforeSend: function () {
                        $(".remove-field").prop("disabled", true);
                    },
                    success: function (response) {
                        if (response.success) {
                            $fieldGroup.remove();
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

        $("#dynamic-fields1").on("click", ".remove-field1", function () {
            
            const fieldCount = $('#dynamic-fields1 .field-group').length;
            const $fieldGroup = $(this).closest(".multiple_add_group");
             const recordId = $fieldGroup.data("record-id");
          
             // Validate fields in the group before deletion
             $fieldGroup.find("input").each(function () {
                 $("#myForm").validate().element(this);
             });
             if((fieldCount==1)){
                 $('#error-message2')
                     .text(`No More Fields to remove.`)
                     .fadeIn()
                     .attr('tabindex', '-1')
                     .focus()
                     .delay(5000)
                     .fadeOut();
                     return false;
             }
             if (recordId && recordId !== "new" && (fieldCount!=1)) {
                 // AJAX request to delete the record
                 $.ajax({
                     url: `/delete_experience/${recordId}`,
                     type: 'DELETE',
                     data: { _token: '{{ csrf_token() }}' },
                     beforeSend: function () {
                         $(".remove-field1").prop("disabled", true);
                     },
                     success: function (response) {
                         if (response.success) {
                             $fieldGroup.remove();
                         } else {
                            $('#error-message2')
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
                        $('#error-message2')
                            .text(`Error occurred while deleting the record.`)
                            .fadeIn()
                            .attr('tabindex', '-1')
                            .focus()
                            .delay(5000)
                            .fadeOut();
                            return false;
                     },
                     complete: function () {
                         $(".remove-field1").prop("disabled", false);
                     },
                 });
             } else {
                 $fieldGroup.remove(); 
             }
          });

    
          $("#dynamic-fields2").on("click", ".remove-field2", function () {
            
            const fieldCount = $('#dynamic-fields2 .field-group').length;
            const $fieldGroup = $(this).closest(".multiple_add_group");
             const recordId = $fieldGroup.data("record-id");
          
             // Validate fields in the group before deletion
             $fieldGroup.find("input").each(function () {
                 $("#myForm").validate().element(this);
             });
             if((fieldCount==1)){
                 $('#error-message3')
                     .text(`No More Fields to remove.`)
                     .fadeIn()
                     .attr('tabindex', '-1')
                     .focus()
                     .delay(5000)
                     .fadeOut();
                     return false;
             }
             if (recordId && recordId !== "new" && (fieldCount!=1)) {
                 // AJAX request to delete the record
                 $.ajax({
                     url: `/delete_certification/${recordId}`,
                     type: 'DELETE',
                     data: { _token: '{{ csrf_token() }}' },
                     beforeSend: function () {
                         $(".remove-field2").prop("disabled", true);
                     },
                     success: function (response) {
                         if (response.success) {
                             $fieldGroup.remove();
                         } else {
                             $('#error-message3')
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
                         $('#error-message3')
                            .text(`Error occurred while deleting the record.`)
                            .fadeIn()
                            .attr('tabindex', '-1')
                            .focus()
                            .delay(5000)
                            .fadeOut();
                            return false;
                     },
                     complete: function () {
                         $(".remove-field1").prop("disabled", false);
                     },
                 });
             } else {
                 $fieldGroup.remove(); 
             }
          });
        // Form Submission Validation (my update integrated here)
        $("#myForm").submit(function (e) {
            let isValid = true;

            // Validate file inputs
            $('input[name="doc_images[]"]').each(function (index) {
                const $input = $(this);
                const hasExistingImage = $input.closest(".multiple_add").find("img.doc_immg").length > 0;

                if (!$input.val() && !hasExistingImage) { // Only require upload if no existing image
                    isValid = false;
                    if (!$input.next(".error").length) {
                        $input.addClass("is-invalid");
                        $input.after(`<label class="error">Please upload a file for document123 ${index + 1}.</label>`);
                    }
                } else {
                    $input.removeClass("is-invalid");
                    $input.next(".error").remove(); // Remove error label when valid
                }
            });


            // Validate qualification inputs
            $('input[name="emp_qualification[]"]').each(function (index) {
                const $input = $(this);
                if (!$input.val()) {
                    isValid = false;
                    if (!$input.next(".error").length) {
                        $input.addClass("is-invalid");
                        $input.after(`<label class="error">Please enter qualification ${index + 1}.</label>`);
                    }
                } else {
                    $input.removeClass("is-invalid");
                    $input.next(".error").remove(); // Remove error label
                }
            });

            // Validate marks inputs
            $('input[name="emp_marks[]"]').each(function (index) {
                const $input = $(this);
                if (!$input.val() || isNaN($input.val())) {
                    isValid = false;
                    if (!$input.next(".error").length) {
                        $input.addClass("is-invalid");
                        $input.after(`<label class="error">Please enter valid marks ${index + 1}.</label>`);
                    }
                } else {
                    $input.removeClass("is-invalid");
                    $input.next(".error").remove(); // Remove error label
                }
            });
            //validate company name
            $('input[name="company_nme[]"]').each(function (index) {
                const $input = $(this);
                if (!$input.val()) {
                    isValid = false;
                    if (!$input.next(".error").length) {
                        $input.addClass("is-invalid");
                        $input.after(`<label class="error">Please enter company name ${index + 1}.</label>`);
                    }
                } else {
                    $input.removeClass("is-invalid");
                    $input.next(".error").remove(); // Remove error label
                }
            });

            // Validate experience inputs
            $('input[name="yr_experience[]"]').each(function (index) {
                const $input = $(this);
                if (!$input.val() || isNaN($input.val())) {
                    isValid = false;
                    if (!$input.next(".error").length) {
                        $input.addClass("is-invalid");
                        $input.after(`<label class="error">Please enter Years of Experience. ${index + 1}.</label>`);
                    }
                } else {
                    $input.removeClass("is-invalid");
                    $input.next(".error").remove(); // Remove error label
                }
            });
                       //validate certification name
            $('input[name="certification[]"]').each(function (index) {
                const $input = $(this);
                if (!$input.val()) {
                    isValid = false;
                    if (!$input.next(".error").length) {
                        $input.addClass("is-invalid");
                        $input.after(`<label class="error">Please enter certification name ${index + 1}.</label>`);
                    }
                } else {
                    $input.removeClass("is-invalid");
                    $input.next(".error").remove(); // Remove error label
                }
            });
            $('input[name="cer_image[]"]').each(function (index) {
                const $input = $(this);
                const hasExistingImage = $input.closest(".multiple_add").find("img.cert_immg").length > 0;

                if (!$input.val() && !hasExistingImage) { // Only require upload if no existing image
                    isValid = false;
                    if (!$input.next(".error").length) {
                        $input.addClass("is-invalid");
                        $input.after(`<label class="error"Please upload certification file ${index + 1}.</label>`);
                    }
                } else {
                    $input.removeClass("is-invalid");
                    $input.next(".error").remove(); // Remove error label when valid
                }
            });

            // Stop form submission if validation fails
            if (!isValid) {
                e.preventDefault();
            } 
        });
        $(".del_doc_img").on("click", function () {
            let id = $(this).attr("id");
            let clickedElement = $(this);
            
            $.ajax({
                url: `/delete_doc_img/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                // beforeSend: function () {
                //     $(".remove-field2").prop("disabled", true);
                // },
                success: function (response) {
                    if (response.success) {
                        $('#success-message1').text(response.success).fadeIn().delay(5000).fadeOut();
                        // Show the file input field
                clickedElement.siblings("input[type='file']").show();

                    // Hide the image and delete button
                    clickedElement.siblings(".doc_immg").hide();
                    clickedElement.hide();

                         // $fieldGroup.remove();
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
                    $(".remove-field1").prop("disabled", false);
                },
            });
        });
        $(".del_cert_img").on("click", function () {
    let id = $(this).attr("id");
    let clickedElement = $(this); // Store reference to the clicked element

    $.ajax({
        url: `/delete_cert_img/${id}`,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function (response) {
            if (response.success) {
                $('#success-message1').text(response.success).fadeIn().delay(5000).fadeOut();

                // Show the file input field
                clickedElement.siblings("input[type='file']").show();

                // Hide the image and delete button
                clickedElement.siblings(".cert_immg").hide();
                clickedElement.hide();
            } else {
                $('#error-message1')
                    .text(`Failed to delete the record.`)
                    .fadeIn()
                    .attr('tabindex', '-1')
                    .focus()
                    .delay(5000)
                    .fadeOut();
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
        },
        complete: function () {
            $(".remove-field1").prop("disabled", false);
        },
    });
});

    });
</script>
          @endsection
         