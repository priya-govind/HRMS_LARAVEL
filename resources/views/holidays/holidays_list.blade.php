@extends('layouts.app')
@section('content')
      
      <!-- partial:partials/_navbar.html -->
      @include('layouts.includes.topbar')
     
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('layouts.includes.sidebar')
        <style>
            .form-control {
            width: auto;
            min-width: 150px;
            max-width: 100%;
            display: inline-block;
            }

            /* Radio buttons inline */
            .leave_type {
            margin-right: 5px;
            }

            .mb-3 {
            margin-bottom: 1rem;
            }

            /* Align labels and inputs neatly */
            .form-label {
            font-weight: bold;
            margin-bottom: 4px;
            display: inline-block;
            }

            /* Textarea sizing */
            textarea#reason {
            width: 100%;
            min-height: 80px;
            resize: vertical;
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
            }

            /* Button styling */
            #saveBtn {
            margin-top: 10px;
            padding: 6px 16px;
            font-size: 14px;
            }
            select.form-control{
                padding: 0.4375rem 0.75rem;
                border: 1px solid #aaa;
                outline: 1px solid #ced4da;
                color: #6c757d;
                width: 75%;
                border-radius: 3px;
                background-color: transparent;
                appearance: auto; /* For most modern browsers */
                -webkit-appearance: auto; /* Safari/Chrome */
                -moz-appearance: auto; /* Firefox */

            }
            </style>
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-header">
                                    <div class="float-start">
                                        Office Holidays List
                                    </div>
                                    @can('PagePermit', ['global.categories', config('global_permissions.Add')])
                                        <div class="float-end">
                                            <button class="btn btn-primary btn-sm permit_popup" id="add_new_holiday">Add New Holiday </button>
                                        </div>
                                    @endcan
                            </div> 
                            <div class="card-body">
                                <table  id="HolidayTable" class="display table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Sno</th>
                                            <th>Holiday</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    
                                    </tbody>
                                </table>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    <div class="modal fade" id="HolidayModal" tabindex="-1" aria-labelledby="HolidayModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content logic_cont">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="HolidayModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="success-message1" class="alert alert-success" role="alert" style="display: none;"></div>
                    <div id="error-message1" class="alert alert-danger" style="display: none;"></div>
                    <form id="HolidayForm">
                        @csrf
                        <input type="hidden" id="recordId" />
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="name" class="form-label">Holiday Name:</label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="holiday_name" name="holiday_name">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="name" class="form-label">From Date:</label>
                            </div>
                            <div class="col-md-6" style="padding: 0 4% 0 1%;">
                                <input type="text" class="form-control ms-2" id="from_dt" name="from_dt" readonly>
                            </div>
                        </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="name" class="form-label">To Date:</label>
                                 </div>
                                <div class="col-md-6"  style="padding: 0 4% 0 1%;">
                                    <input type="text" class="form-control ms-2" id="to_dt" name="to_dt">
                                </div>
                            </div>
                      <div class="row mb-3">  
                        <div class="col-md-12 text-center">
                            <button type="Submit" class="btn btn-success text-center" >Submit</button>
                        </div>
                      </div>
                    </form>
                </div>
            </div>
        </div>
    </div> 
     <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
            </div>
        </div>
    </div>
<script src="{{ asset('assets/js/common.js') }}"></script>
<script src="{{ asset('assets/js/leaves_mgmt.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
     const myModal = new bootstrap.Modal(document.getElementById('HolidayModal'), {
                backdrop: 'static',
                keyboard: false
            });
    $(document).on('click', '#add_new_holiday', function () {
        $('#HolidayModalLabel').text("Add New Holiday");
         $('#HolidayForm')[0].reset();
            myModal.show();
    });
 $(document).off('click', '.editButton').on('click', '.editButton', function () {
    const id = $(this).data('id');
        title='Edit Holiday';
    $.get('/holidays/' + id+'/edit', function (data) {
        $('#HolidayForm')[0].reset();
        $('#HolidayModalLabel').text(title);
        $('#recordId').val(data.id);
        $('#holiday_name').val(data.holiday_name);
        $('#from_dt').val(data.from_dt);
        $('#to_dt').val(data.to_dt);
         if (data.from_dt) {
            var dateObj = $.datepicker.parseDate("dd-mm-yy", data.from_dt);
            $("#to_dt").datepicker("option", "minDate", dateObj);
        }
        myModal.show();
    });
});

      $("#from_dt,#to_dt").datepicker({
            dateFormat: "dd-mm-yy",
            onSelect: function(selectedDate) {
                var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate); // Parse selected date
                var minDate = new Date(dateObj);
                minDate.setDate(minDate.getDate() ); // Ensure endDate is at least 1 day ahead
                $("#to_dt").datetimepicker("option", "minDate", minDate);
            }
        });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
        $(document).on('click', '.delete-btn', function () {
            const itemId = $(this).data('id'); // Get item ID from the button
            const url = '/holidays/' + itemId;
            const myModal = new bootstrap.Modal(document.getElementById('confirmationModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    myModal.show();
            // Add click listener for the Delete button inside the modal
            $('#confirmDelete').off('click').on('click', function () {
                deleteItem(itemId,url,'#HolidayTable'); // Call your delete function
                myModal.hide();
            });
        });
$('#HolidayForm').validate({
        rules: {
            holiday_name: {
                required: true
            },
            from_dt: {
                required: true
            },
        },
        highlight: function (element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        },
        messages: {
            holiday_name: "Please Enter Holiday Name",
            from_dt: "Please Select From Date."
        },
       submitHandler: function (form) {
            const method_pass = $('#recordId').val() ? '&_method=PUT' : '';
            const formData = $(form).serialize() + method_pass;
            const url = $('#recordId').val() ? 'holidays/' + $('#recordId').val() : 'holidays';
            const method = 'POST';
            $.ajax({
                url: url,
                type: method,
                data: formData,
                beforeSend: function () {
                    $('#saveBtn').prop('disabled', true).text('Saving...');
                     $('.sender_load').show();
                },
                success: function (response) {
                    $('#success-message1').text(response.message).fadeIn().delay(1000).fadeOut();


                setTimeout(function () {
                    $('#success-message1').hide();
                    myModal.hide();
                    var table = $('#HolidayTable').DataTable();
                   table.ajax.reload(null, false);
                    $('#HolidayForm').find('input, textarea, select').each(function () {
                        if ($(this).is(':checkbox') || $(this).is(':radio')) {
                            $(this).prop('checked', false);
                        } else {
                            $(this).val('');
                        }
                    });
                }, 3000);
                },
                error: function (xhr) {
                    const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    $('#error-message1').text('Error: ' + errorMessage).fadeIn().delay(5000).fadeOut();
                },
                complete: function () {
                    $('#saveBtn').prop('disabled', false).text('Save');
                    $('.sender_load').hide();
                }
            });
        }
    });
    $('#HolidayTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
       ajax: {
              url: "{{ route('holidays.index') }}",
              type: "GET",
              data: function (d) {
                  d.from_dt = $('#from_dt_search').val();
                  d.to_dt = $('#to_dt_search').val();
              }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false  },
            { data: 'holiday_name', name: 'holiday_name' },
            { data: 'holiday_dtls', name: 'holiday_dtls' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        error: function (xhr, error, thrown) {
            alert('Something went wrong while loading the data.');
        }
    });
});
</script>

          @endsection
         