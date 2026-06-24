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
                <div class="card-header bg-primary text-white">
                  <h5 class="mb-0">Export Attendance Report</h5>
                </div>
                <div class="card-body">
                  <form method="GET" action="{{ route('attendance.export') }}">
                    <div class="row align-items-center mb-3">
                      <div class="col-md-1">
                        <label class="form-label mb-0" for="Search">Search:</label>
                      </div>
                      <div class="form-group col-md-3">
                        <input type="text" class="form-control" id="startDate" name="start_date" placeholder="Start Date" readonly>
                      </div>
                      <div class="form-group col-md-3">
                        
                        <input type="text" class="form-control" id="endDate" name="end_date" placeholder="End Date" readonly>
                      </div>
                      <div class="form-group col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Export</button>
                      </div>
                    </div>
                  </form>
                  @if(isset($dateRange) && $dateRange!='')
                    <h4>Attendance Report</h4>
                    <h4>2025 June 78th Week Attendance of FORTIGRID - Dev Team</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            @foreach($dateRange as $date)
                                <th colspan="4">{{ \Carbon\Carbon::parse($date)->format('D d') }}</th>
                            @endforeach
                            <th>Week Days Leave</th>
                        </tr>
                        <tr>
                            <th></th>
                            @foreach($dateRange as $date)
                                <th>IN</th>
                                <th>OUT</th>
                                <th>Worked</th>
                                <th>Status</th>
                            @endforeach
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td>{{ $employee->name }}</td>
                              
                                @foreach($dateRange as $date)
                                    @php
                                        $attn = $employee->attendances
                                            ->where('chkinDate', '>=', $date . ' 00:00:00')
                                            ->where('chkinDate', '<=', $date . ' 23:59:59')
                                            ->first();
                                    @endphp
                                    <td>{{ $attn ? date('H:i', strtotime($attn->chkinDate)) : '' }}</td>
                                    <td>{{ $attn ? date('H:i', strtotime($attn->chkoutDate)) : '' }}</td>
                                    <td>{{ $attn ? floor($attn->work_duration / 60) . 'h ' . ($attn->work_duration % 60) . 'm' : '' }}</td>
                                    <td>{{ $attn ? ($attn->workingMode->work_mode_name ?? 'Unknown') : 'Absent' }}</td>
                                @endforeach
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
              @endif
                </div>
              </div>
              
          </div>
           

            </div>
        </div>
          @endsection
         