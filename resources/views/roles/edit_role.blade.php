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
        <div class="card">
            <div class="card-header">
                <div class="float-start">
                    Edit Role
                </div>
                <div class="float-end">
                    <a href="{{ route('roles') }}" class="btn btn-primary btn-sm">&larr; Back</a>
                </div>
            </div>
            <div class="card-body">
            <nav class="p-1">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('roles') }}">Roles</a></li>
                <li class="breadcrumb-item active">Edit Role</li>
            </ol>
        </nav>
            @if (session('success'))
                        <div class="alert alert-success" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            {{ session('success') }}
                        </div>
                    @endif
                    <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                    <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                <form action="{{route('update_role',$role->id)}}" method="post" id="myform">
                    @csrf

                    <div class="mb-3 row">
                        <label for="role_name" class="col-md-4 col-form-label text-md-end text-start">Role Name</label>
                        <div class="col-md-3">
                          <input type="text" class="form-control @error('role_name') is-invalid @enderror" id="role_name" name="role_name" value="{{$role->role_name}}">
                            @error('role_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-floating">
                        Site Permissions:
                        <hr/>
                    <table  class="table table-bordered">
                        <thead>
                            <tr>
                            <th>Category</th>
                            @foreach ($permissions as $permission)
                                <th>{{ $permission->permission_name }}</th>
                            @endforeach
                                </tr>
                        </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td><b>{{ $category->category_name }}</b></td>
                                        @foreach ($permissions as $permission)
                                        @if ($loop->first)
                                        <td>
                                                <input type="checkbox" 
                                                    name="permissions[{{ $category->id }}][{{ $permission->id }}]" {{ isset($existingPermissions[$category->id]) && in_array($permission->id, $existingPermissions[$category->id]) ? 'checked' : '' }}
                                                    value="1"  class="row-check parent_check">
                                            </td>
                                        @else
                                        <td>
                                                <input type="checkbox" 
                                                    name="permissions[{{ $category->id }}][{{ $permission->id }}]" {{ isset($existingPermissions[$category->id]) && in_array($permission->id, $existingPermissions[$category->id]) ? 'checked' : 'disabled' }}
                                                    value="1" class="item-check parent_check">
                                            </td>
                                        @endif
                                            
                                        @endforeach

                                    </tr>
                                    @foreach ($category->children as $subCategory) 
                                    <tr>
                                        <td>{{ $subCategory->category_name }}</td>
                                        @foreach ($permissions as $permission)
                                        @if ($loop->first)
                                            <td>
                                                <input type="checkbox" 
                                                    name="permissions[{{ $subCategory->id }}][{{ $permission->id }}]" 
                                                    {{ isset($existingPermissions[$subCategory->id]) && in_array($permission->id, $existingPermissions[$subCategory->id]) ? 'checked' : '' }}
                                                    value="1" class="row-check child_check">
                                            </td>
                                        @else
                                            <td>
                                                <input type="checkbox" 
                                                    name="permissions[{{ $subCategory->id }}][{{ $permission->id }}]" 
                                                    {{ isset($existingPermissions[$subCategory->id]) && in_array($permission->id, $existingPermissions[$subCategory->id]) ? 'checked' : 'disabled' }}
                                                    value="1" class="item-check child_check">
                                            </td>
                                        @endif
                                        @endforeach
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                    </table>
                    </div>
                    <div class="mb-3 row">
                        <input type="submit" class="col-md-3 offset-md-5 btn btn-primary" value="Save">
                    </div>
                    
                </form>
            </div>
        </div>
          </div>
          <script type="text/javascript">
$(document).ready(function () {
    // Enable or disable child checkboxes based on parent checkbox
    $('.parent_check').on('change', function () {
        const parentRow = $(this).closest('tr'); // Find the parent row
        const childCheckboxes = parentRow.nextUntil('tr.parent').find('.child_check'); // Locate child checkboxes

        if ($(this).is(':checked')) {
            childCheckboxes.prop('disabled', false); // Enable child checkboxes
        } else {
            childCheckboxes.prop('checked', false).prop('disabled', true); // Disable and uncheck child checkboxes
        }
    });
/**Checkbox concept for select all 
 * once view checkbox selected remaining permissions need to be selected and viceversa.
 */
    $('.row-check').click(function(){
      if (this.checked) {
        $(this).closest('tr').find('.item-check').attr('disabled', false);
      } else {
        //jq(this).closest('tr').find('.item-check').prop('checked', false).attr('disabled', true);
        $(this).closest('tr').find('.item-check').attr('disabled', true);
      }	
   });
    // Validate on form submission
    $("#myform").validate({
        rules: {
            role_name: {
                required: true
            }
        },
        messages: {
            role_name: "Please enter the Role Name."
        },
        errorPlacement: function (error, element) {
            // Place error message after the input field
            error.insertAfter(element);
        },
        highlight: function (element) {
            // Add 'is-invalid' class on validation fail
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            // Remove 'is-invalid' class on validation success
            $(element).removeClass('is-invalid');
        },
        submitHandler: function (form) {
            let isValid = false;

            // Check if at least one checkbox is selected
            $('input[type="checkbox"]').each(function () {
                if ($(this).is(':checked')) {
                    isValid = true; 
                    return false;
                }
            });

            if (!isValid) {
                $('#error-message1')
                    .text('Please select at least one permission before submitting the form.')
                    .fadeIn()
                    .attr('tabindex', '-1')
                    .focus()
                    .delay(5000)
                    .fadeOut();

                return false;
            }

            // Validate parent-child relationships
            let parentValidationFailed = false;

            $('.parent_check').each(function () {
                const parentRow = $(this).closest('tr');
                const childCheckboxes = parentRow.nextUntil('tr.parent').find('.child_check');

                if ($(this).is(':checked') && childCheckboxes.filter(':checked').length === 0) {
                    $('#error-message1')
                    .text(`Please select at least one child permission for parent: ${parentRow.find('td:first').text()}`)
                    .fadeIn()
                    .attr('tabindex', '-1')
                    .focus()
                    .delay(5000)
                    .fadeOut();
                    parentValidationFailed = true;
                    return false;
                }
            });

            if (parentValidationFailed) {
                return false;
            }

            form.submit(); // Submit the form if everything is valid
        }
    });
});

</script>
          @endsection
         