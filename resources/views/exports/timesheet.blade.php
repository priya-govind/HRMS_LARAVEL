<table>
    <thead>
        <tr><td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="2" style="font-weight:bolder;text-align: center; vertical-align: middle;">Work Report</td>
            @if(!empty($emp_name))
 <td colspan="2"  style="font-weight:bolder;text-align: center; vertical-align: middle;">Emp Name:{{ $emp_name }}</td>
            @endif
           
        </tr>
        <tr>
            <th style="font-weight:bold;background-color:#215dab;color: white;">Date</th>
            <th style="font-weight:bold;background-color:#215dab;color: white;">Day</th>
            @if(!empty($status_type) && $status_type==2)
                <th style="width:150px;font-weight:bold;background-color:#215dab;color: white;">Employee Name</th>
            @endif
            <th style="width:150px;font-weight:bold;background-color:#215dab;color: white;">Project</th>
            <th style="width:150px;font-weight:bold;background-color:#215dab;color: white;">Module</th>
            <th style="width:150px;font-weight:bold;background-color:#215dab;color: white;">Task</th>
            <th style="width:150px;font-weight:bold;background-color:#215dab;color: white;">Start Time</th>
            <th style="width:150px;font-weight:bold;background-color:#215dab;color: white;">End Time</th>
            <th style="width:150px;font-weight:bold;background-color:#215dab;color: white;">Hours Spent</th>
            <th style="width:950px;font-weight:bold;background-color:#215dab;color: white;">Comments</th>
        </tr>
    </thead>
<tbody>

@foreach($grouped as $date => $data)

    @php $rowCount = count($data['rows']); @endphp

    @foreach($data['rows'] as $index => $row)

        @php
            $minutes = $row->duration;
            if($minutes){
                if ($minutes < 60) {
                    $duration = $minutes . ' mins';
                } else {
                    $hours = floor($minutes / 60);
                    $remainingMinutes = $minutes % 60;

                    $duration = $remainingMinutes > 0
                        ? $hours . ' hr ' . $remainingMinutes . ' mins'
                        : $hours . ' hr';
                }
            } else {
                 $duration = '-';
            }
            
        @endphp

        <tr>
            @if($index === 0)
                <td rowspan="{{ $rowCount }}">{{ $date }}</td>
                <td rowspan="{{ $rowCount }}">{{ $data['day'] }}</td>
            @endif

            @if(!empty($status_type) && $status_type==2)
                <td>{{ optional($row->employee)->name ?? 'N/A' }}</td>
            @endif

            <td>{{ $row->custom_project ?? optional($row->Projects)->proj_name ?? 'N/A' }}</td>
            <td>{{ $row->custom_module ?? optional($row->module)->module_name ?? 'N/A' }}</td>
            <td>{{ $row->custom_task ?? optional($row->task)->task_name ?? 'N/A' }}</td>

            <td>{{ \Carbon\Carbon::parse($row->from_time)->format('h:i A') }}</td>
            <td>{{ \Carbon\Carbon::parse($row->to_time)->format('h:i A') }}</td>
            <td>{{ $duration }}</td>

            <td style="word-wrap: break-word;">
                {{ wordwrap($row->comments, 60, "\n", true) }}
            </td>
        </tr>

    @endforeach

@endforeach

{{-- TOTAL --}}
<tr>
    @if(!empty($status_type) && $status_type==2)
        <td colspan="8" align="right"><b>Total Hours Spent:</b></td>
    @else 
        <td colspan="7" align="right"><b>Total Hours Spent:</b></td>
    @endif

    <td><b>{{ $total_time }}</b></td>
</tr>

{{-- HOLIDAY SECTION --}}
    @if(!empty($holidays))

    <tr><td colspan="10" style="height:20px;font-weight:bold;">{{ $emp_name }} took leave on following dates as follows:</td></tr>

    <tr>
        <th colspan="2" style="background-color:#215dab;color:white;">Date</th>
        <th style="background-color:#215dab;color:white;">Day</th>
    </tr>

    @foreach($holidays as $holiday)
    <tr>
        <td colspan="2">{{ $holiday['date'] }}</td>
        <td>{{ $holiday['day'] }}</td>
    </tr>
    @endforeach

    @endif

    {{-- Office Holidays: --}}

    {{-- Office Holidays: --}}
    @if(!empty($office_holidays))
        <tr><td colspan="10" style="height:20px;font-weight:bold;">Office Holidays:</td></tr>

        <tr>
            <th colspan="2" style="background-color:#215dab;color:white;">Date</th>
            <th style="background-color:#215dab;color:white;">Day</th>
            <th style="background-color:#215dab;color:white;">Reason</th>
        </tr>
        @foreach($office_holidays as $date => $name)
        <tr>
            <td colspan="2">{{ $date }}</td>
            <td>{{ \Carbon\Carbon::createFromFormat('d-m-Y', $date)->format('l') }}</td>
            <td>{{ $name }}</td>
        </tr>
        @endforeach
    @endif

</tbody>
</table>
