<?php
namespace App\Exports;

use App\Models\Timesheet;
use App\Models\Holiday;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;
use Carbon\CarbonPeriod;

class TimesheetExport implements FromView
{
    protected $from;
    protected $to;
    protected $emp_id;
    protected $emp_name;
    protected $given_dt;
    protected $proj_id;
    protected $module_id;
    protected $status_type;
public function __construct(Request $request)
{
    $this->from = $request->FromDate;
    $this->to = $request->ToDate;
    $this->emp_id = $request->emp_id;
    $this->emp_name = $request->emp_name;
    $this->given_dt = $request->GivenDate;
    $this->status_type = $request->status_type;
    $this->proj_id = $request->proj_id;
    $this->module_id = $request->module_id;
}


 public function view(): View
{  
   $query = Timesheet::with(['Projects','employee','module','task']);

        // Employee filter
        if (!empty($this->emp_id) && $this->status_type == '2') {
            $query->where('emp_id', $this->emp_id);
        }

        // Date defaults
        $from_dt = Carbon::now()->startOfWeek()->toDateString();
        $to_dt   = Carbon::now()->endOfWeek()->toDateString();

        // Date filters
        if ($this->given_dt) {

            $from_dt = Carbon::parse($this->given_dt)->format('Y-m-d');
            $to_dt   = $from_dt;

            $query->whereDate('create_dt', $from_dt);

        } elseif ($this->from && $this->to) {

            $from_dt = Carbon::parse($this->from)->format('Y-m-d');
            $to_dt   = Carbon::parse($this->to)->format('Y-m-d');

            $query->whereBetween('create_dt', [$from_dt, $to_dt]);

        } else {
            $query->whereBetween('create_dt', [$from_dt, $to_dt]);
        }

        // Employee mode
        if ($this->status_type == '2') {
            $query->where('emp_id', '!=', session('user_id'));
        } else {
            $query->where('emp_id', session('user_id'));
        }

        // Filters
        if (!empty($this->proj_id)) {
            $query->where('project_id', $this->proj_id);
        }
        if (!empty($this->module_id)) {
            $query->where('module_id', $this->module_id);
        }

        // Fetch
        $history = $query->orderBy('create_dt')
                        ->orderBy('id')
                        ->get();

        /**Office Holidays */
           $officeHolidays = Holiday::where(function($q) use ($from_dt, $to_dt) {
                $q->whereBetween('from_dt', [$from_dt, $to_dt])
                ->orWhere(function($q2) use ($from_dt, $to_dt) {
                    $q2->where('from_dt', '>=', $from_dt)
                        ->where(function($q3) use ($to_dt) {
                            $q3->where('to_dt', '<=', $to_dt)
                                ->orWhereNull('to_dt'); // ✅ handle single-day holidays
                        });
                });
            })->get();

            $holidayDates = [];
            foreach ($officeHolidays as $holiday) {
                $start = $holiday->from_dt;
                $end   = $holiday->to_dt ?? $holiday->from_dt; // ✅ fallback

                $period = CarbonPeriod::create($start, $end);
                foreach ($period as $date) {
                    $holidayDates[$date->format('d-m-Y')] = $holiday->holiday_name;
                }
            }
        /**Office Holidays */


// ================== ✅ CORE LOGIC ==================

$start = Carbon::parse($from_dt);
$end   = Carbon::parse($to_dt);

// Prevent future dates
$today = Carbon::today();
if ($end->gt($today)) {
    $end = $today;
}

// Create range
$period = CarbonPeriod::create($start, $end);

// Prepare structures
$grouped  = [];
$holidays = [];

// Initialize all weekdays
foreach ($period as $date) {

    if ($date->isWeekend()) continue;

    $key = $date->format('d-m-Y');

    $grouped[$key] = [
        'day'  => $date->format('l'),
        'rows' => []
    ];
}

// Map entries
foreach ($history as $row) {
    $key = Carbon::parse($row->create_dt)->format('d-m-Y');

    if (isset($grouped[$key])) {
        $grouped[$key]['rows'][] = $row;
    }
}

// Extract holidays
foreach ($grouped as $date => $data) {
   // Skip if date is an office holiday
    if (array_key_exists($date, $holidayDates)) {
        continue;
    }

    if (empty($data['rows']) && $date != $today->format('d-m-Y')) {
        $holidays[] = [
            'date' => $date,
            'day'  => $data['day']
        ];
    }
}

// Total time
$totalMinutesRaw = $history->sum('duration');

$hours   = intdiv($totalMinutesRaw, 60);
$minutes = $totalMinutesRaw % 60;

$total_time = $minutes > 0
    ? sprintf('%02dhrs %02dminutes', $hours, $minutes)
    : sprintf('%02dhrs', $hours);


// ================== ✅ RETURN ==================     
return view('exports.timesheet', [
                                    'grouped'     => $grouped,
                                    'holidays'    => $holidays,
                                    'emp_name'    => $this->emp_name ?? session('user_name'),
                                    'status_type' => $this->status_type,
                                    'total_time'  => $total_time,
                                    'from_date'   => $from_dt,
                                    'to_date'     => $end->toDateString(),
                                    'office_holidays' => $holidayDates,
                                ]);
}
}