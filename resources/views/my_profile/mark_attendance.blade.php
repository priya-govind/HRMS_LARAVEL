@extends('layouts.app')
@section('content')
      
      <!-- partial:partials/_navbar.html -->
      @include('layouts.includes.topbar')
     
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('layouts.includes.sidebar')
        
        <!-- partial -->
        <div class="main-panel">
        <div class="content-wrapper">
        <form id="myForm">
    <div class="form-group">
        <label for="emp_status">Employee Status: &nbsp;&nbsp;</label>
        <input type="radio" name="emp_status" id="emp_status_active" value="1"> Active &nbsp;&nbsp;&nbsp;
        <input type="radio" name="emp_status" id="emp_status_inactive" value="0"> Inactive
    </div>
    <div id="emp_status-error" class="error" style="display:none;"></div>
    <button type="submit">Submit</button>
</form>
          </div>
          <script>
            $(document).ready(function() {
    $("#myForm").validate({
        rules: {
            emp_status: {
                required: true
            }
        },
        messages: {
            emp_status: {
                required: "Please select an employee status."
            }
        },
        errorPlacement: function(error, element) {
            if (element.attr("name") === "emp_status") {
                $("#emp_status-error").html(error).show();
            } else {
                error.insertAfter(element);
            }
        }
    });

    // Hide error when selection is made
    $("input[name='emp_status']").on("change", function() {
        $("#emp_status-error").hide();
    });
});
          </script>
          @endsection
         