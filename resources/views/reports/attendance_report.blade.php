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
            <div class="row justify-content-center mt-4">
          <div class="col-md-12">
              <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: #215dab">
                  <h5 class="mb-0">Export Attendance Report</h5>
                </div>
                <div class="card-body">
                  <form method="POST" action="{{ route('attendance_export') }}" id="AttendanceReport">
                    @csrf
                    <div class="row form-row align-items-start">
                      <div class="form-group col-md-2">
                        <label for="startDate">Start Date</label>
                        <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Start Date" value="{{ request('start_date') }}"  readonly>
                      </div>
                      <div class="form-group col-md-2">
                        <label for="endDate">End Date</label>
                        <input type="text" class="form-control" id="end_date" name="end_date" placeholder="End Date" value="{{ request('end_date') }}" readonly>
                      </div>
                      <div class="form-group col-md-2 d-flex align-items-end" style="margin: 23px 0 0 0;">
                        <button type="submit" class="btn btn-primary w-100">Generate</button>
                      </div>
                    @if(request('start_date'))
                      <div class="form-group col-md-2 d-flex align-items-end" style="margin: 23px 0 0 0;">
                        {{-- <button class="btn btn-primary download_attendance">Export to excel 
                          <i class="fa fa-download" aria-hidden="true"></i>
                        </button> --}}
                        <button  type="button" class="btn btn-primary download_attendance" 
                                      data-start="{{ request('start_date') }}" 
                                      data-end="{{ request('end_date') }}" 
                                      data-team="{{ request('team_type_id') }}"> 
                                      Export to excel  <i class="fa fa-download" aria-hidden="true"></i>
                        </button>

                      </div>
                    @endif
                    </div>
                  </form>
                  @if(isset($dateRange) && $dateRange!='')
                    <div class="container">
                      <div style="overflow: auto;">
                        <h4>{{ $title }}</h4>
                          <table class="table table-bordered">
                            <thead>
                                <tr style="background: skyblue;">
                                    <th>Employee Name</th>
                                    @foreach($dateRange as $date)
                                     @if(!in_array(\Carbon\Carbon::parse($date)->format('l'), ['Saturday', 'Sunday']))
                                        <th colspan="4">{{ \Carbon\Carbon::parse($date)->format('D d') }}</th>
                                        @endif
                                    @endforeach
                                    <th>Week Days Leave</th>
                                </tr>
                                <tr style="background:lightgray;"> 
                                    <th></th>
                                    @foreach($dateRange as $date)
                                      @if(!in_array(\Carbon\Carbon::parse($date)->format('l'), ['Saturday', 'Sunday']))
                                        <th>IN</th>
                                        <th>OUT</th>
                                        <th>Worked</th>
                                        <th>Status</th>
                                        @endif
                                    @endforeach
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                               <?php foreach($employees as $employee) {
                                $i=0;?>
                                    <tr>
                                        <td style="background: skyblue;">{{ $employee->name }}</td>
                                      
                                        @foreach($dateRange as $date)
                                         @if(!in_array(\Carbon\Carbon::parse($date)->format('l'), ['Saturday', 'Sunday']))
                                            @php
                                                   $attn = $employee->attendances
                                                            ->where('chkinDate', '>=', $date . ' 00:00:00')
                                                            ->where('chkinDate', '<=', $date . ' 23:59:59')
                                                            ->first();

                                                        if ($attn) {
                                                            $chkin  = $attn->chkinDate ? \Carbon\Carbon::parse($attn->chkinDate) : null;
                                                            $chkout = $attn->chkoutDate
                                                                ? \Carbon\Carbon::parse($attn->chkoutDate)
                                                                : ($chkin ? \Carbon\Carbon::parse($chkin->format('Y-m-d') . ' 18:00') : null);

                                                            $minutesWorked = ($chkin && $chkout) ? $chkin->diffInMinutes($chkout) : 0;
                                                            $worked = ($chkin && $chkout)
                                                                ? intdiv($minutesWorked, 60) . 'h ' . ($minutesWorked % 60) . 'm'
                                                                : '';
                                                        } else {
                                                            $chkin = $chkout = null;
                                                            $worked = '';
                                                        }
                                            @endphp
                                            <td>{{ $attn ? date('H:i', strtotime($attn->chkinDate)) : '' }}</td>
                                            <td>{{ $attn ? date('H:i', strtotime($chkout)) : '' }}</td>
                                            <td>{{ $worked }}.</td>
                                            @if ($attn && $attn->workingMode)
                                                  <td style="background:{{ config('global_working_mode.' . $attn->workingMode->id) }}">
                                                  </td>
                                              @else
                                              <?php $i++; ?>
                                                  <td style="background: #ff0000;"></td>
                                              @endif
                                              @endif
                                        @endforeach
                                        <td>
                                          @if($i!=0)
                                          {{ $i }} Days
                                          @endif
                                        </td>
                                    </tr>
                                  <?php } ?>
                            </tbody>
                          </table>
                      </div>
                          <br/><br/>
                    <table> 
                      <tr><td style="background:#b4e5a2;width: 70px;"></td><td>Work From Office</td></tr>
                      <tr><td></td></tr>
                      <tr><td style="background:#d86ecc;width: 70px;"></td><td>Work From Home</td></tr>
                      <tr><td></td></tr>
                      <tr><td style="background:#f2aa84;width: 70px;"></td><td>1/2 Day</td></tr>
                      <tr><td></td></tr>
                      <tr><td style="background:#ffff00;width: 70px;"></td><td>Present not Punching</td></tr>
                      <tr><td></td></tr>
                      <tr><td style="background:#ff0000;width: 70px;"></td><td>Absent</td></tr>
                    </table>
                    </div>
                  @endif
                </div>
              </div>
          </div>
            </div>
        </div>

     
   <script>
 

    $("#start_date").datepicker({
        dateFormat: "dd-mm-yy",
        onSelect: function(selectedDate) {
            var dateObj = $.datepicker.parseDate("dd-mm-yy", selectedDate);
            var minDate = new Date(dateObj);
            minDate.setDate(minDate.getDate() + 1);
            $("#end_date").datepicker("option", "minDate", minDate);
        }
    });

    $("#end_date").datepicker({
        dateFormat: "dd-mm-yy"
    });
            // Initialize validation
    $("#AttendanceReport").validate({
        errorClass: "is-invalid", 
        rules: {
            start_date: {
                required: true,
            },
            end_date: {
                required: true,
            },
        },
        messages: {
            start_date: {
                required: "Start Date is Required.",
            },
            end_date: {
                required: "End Date is Required.",
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
             form.submit(); 
        },
    });
 
   // window.onload = function() {
 $('.download_attendance').on('click',function(){
    const start = "{{ request('start_date') }}";
    const end = "{{ request('end_date') }}";

   // const downloadUrl = `/attendance_export_excel?start_date=${start}&end_date=${end}&team_type_id=${team}`;
    const downloadUrl = `{{ url('attendance_export_excel') }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&team_type_id={{ request('team_type_id') }}`;
    console.log(downloadUrl);
   // return false;
window.location.href = downloadUrl;

   // window.location.href = downloadUrl;
     // Trigger download in background
        // const link = document.createElement('a');
        // link.href = downloadUrl;
        // link.setAttribute('download', '');
        // document.body.appendChild(link);
        // link.click();
        // document.body.removeChild(link);
 });


      
  var currentPath = window.location.pathname;
            $('.nav-link').removeClass('active');
            $('a[href="/attendance_report"]').addClass('active');
            $('a.nav-link[href="#reports"]').closest("li.nav-item").addClass("active");


	        
   // };

</script>

<style>
  .form-control:disabled, .form-control:read-only {
    background-color: white;
  }
</style>

        
          @endsection 
         