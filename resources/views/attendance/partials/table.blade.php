@php
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;

    $firstDate = $records->min('punch_date');
    $lastDate  = $records->max('punch_date');
    $weekdays  = ['Monday','Tuesday','Wednesday','Thursday','Friday'];

    $weekDates = collect(CarbonPeriod::create($firstDate, $lastDate))
        ->filter(fn($d) => in_array($d->format('l'), $weekdays))
        ->map(fn($d) => [
            'label' => strtoupper($d->format('l')).' '.$d->format('jS'),
            'date'  => $d->format('Y-m-d'),
        ])
        ->values();

    $teams = $records->groupBy('team_type');
@endphp

@foreach($teams as $team => $entries)
    <h4 class="mt-3">{{ $team }} Team</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th rowspan="2" class="report_days">Employee Name</th>
                @foreach($weekDates as $day)
                    <th colspan="4" class="report_days">{{ $day['label'] }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach($weekDates as $day)
                    <th class="report_state" style="">IN</th>
                    <th class="report_state">OUT</th>
                    <th class="report_state">Worked</th>
                    <th class="report_state">Status</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($entries->groupBy('employee_name') as $employee => $punches)
                @php
                    $punchMap = $punches->keyBy(fn($p) => Carbon::parse($p->punch_date)->format('Y-m-d'));
                @endphp
                <tr>
                    <td>{{ $employee }}</td>
                    @foreach($weekDates as $day)
                        @php
                            $p = $punchMap->get($day['date']);
                        @endphp
                        <td>{{ $p?->checkin_time ? Carbon::parse($p->checkin_time)->format('H:i') : '' }}</td>
                        <td>{{ $p?->checkout_time ? Carbon::parse($p->checkout_time)->format('H:i') : '' }}</td>
                        <td>{{ $p?->duration && $p->duration != '00:00:00' ? Carbon::parse($p->duration)->format('H:i') : '' }}</td>
                        <td>
                            <select class="attendance-status_report" data-id="{{ $p?->id }}">
                                <option value="P" {{ $p?->status == 'P' ? 'selected' : '' }}>P</option>
                                <option value="A" {{ $p?->status == 'A' ? 'selected' : '' }}>A</option>
                                <option value="WFH" {{ $p?->status == 'WFH' ? 'selected' : '' }}>WFH</option>
                                <option value="½P" {{ $p?->status == '½P' ? 'selected' : '' }}>½P</option>
                            </select>
                        </td>
                    @endforeach
                    
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
