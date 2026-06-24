<?php 
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Holiday;
use App\Models\PunchAttendance;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use Illuminate\Support\Str;
class AttendanceReportExport implements FromArray, WithStyles,WithColumnWidths
{
    protected $records;
    protected $weekDates; 
    protected $fromDate;
    protected $toDate;
    protected $statusColors = [
                                'P'      => 'b4e5a2',
                                'WFH'    => 'd86ecc',
                                '½P'     => 'f2aa84',
                                'A'      => 'FF0000',
                                'Absent' => 'FF0000',
                                'OFC' => 'FF860D'
                            ];

    public function __construct($records,$fromDate = null, $toDate = null)
    {
        $this->records = $records;
         $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }
public function array(): array
{
    $data = [];
    $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    $firstDate = $this->records->min('punch_date');
    $lastDate  = $this->records->max('punch_date');

    $startDate = Carbon::parse($firstDate);
    $year  = $startDate->format('Y');
    $month = $startDate->format('F');

    $weekOfMonth = (int) ceil($startDate->day / 7);
    $weekLabel = match ($weekOfMonth) {
        1 => '1st week',
        2 => '2nd week',
        3 => '3rd week',
        4 => '4th week',
        default => "{$weekOfMonth}th week"
    };

    $title = "{$weekLabel} of {$month} - {$year} Attendance of FORTIGRID - ";

    //  Holiday Map
    $holidayMap = [];
    foreach (Holiday::all() as $h) {
        $from = Carbon::parse($h->from_dt);
        $to   = $h->to_dt ? Carbon::parse($h->to_dt) : $from;

        foreach (CarbonPeriod::create($from, $to) as $d) {
            $holidayMap[$d->format('Y-m-d')] = $h->holiday_name;
        }
    }

    //  Week Dates
    $this->weekDates = collect(CarbonPeriod::create($firstDate, $lastDate))
        ->filter(fn($d) => in_array($d->format('l'), $weekdays))
        ->map(fn($d) => [
            'label' => strtoupper($d->format('l')) . ' ' . $d->format('jS'),
            'date'  => $d->format('Y-m-d'),
        ])
        ->values();

    $teams = $this->records->groupBy('team_type');

    foreach ($teams as $team => $entries) {

        // Title
        $data[] = [" $title {$team} Team"];

        // Headers
        $header1 = ['Employee Name'];
        $header2 = [''];

        foreach ($this->weekDates as $day) {
            $header1[] = $day['label'];
            $header1[] = ''; $header1[] = ''; $header1[] = '';
            $header2[] = 'IN'; $header2[] = 'OUT'; $header2[] = 'Worked'; $header2[] = 'STATUS';
        }

        $header1[] = 'Week Days Leave';
        $header1[] = 'Total days leave';
        $header2[] = '';
        $header2[] = '';

        $data[] = $header1;
        $data[] = $header2;

        // Employees
        $employees = $entries->where('employee_code','!=','202')->groupBy('employee_name');

        foreach ($employees as $employee => $punches) {

            $row = [Str::of($employee)->explode(' ')->first()];
            $weeklyLeaveCount = 0;

            $employeeCode = $punches->first()->employee_code;
            $monthStartDate = Carbon::parse($firstDate);
            $totalLeaveCount = PunchAttendance::where('employee_code', $employeeCode)
                ->whereMonth('punch_date', $monthStartDate->month)
                ->whereYear('punch_date', $monthStartDate->year)
                ->whereIn('status', ['A', 'Absent'])
                ->count();

            $punchMap = $punches->keyBy(fn($p) =>
                Carbon::parse($p->punch_date)->format('Y-m-d')
            );

            foreach ($this->weekDates as $day) {

                $date = $day['date'];

                //  Holiday
                if (isset($holidayMap[$date])) {
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                    $row[] = $holidayMap[$date];
                    continue;
                }

                $p = $punchMap->get($date);

                if ($p) {
                    $row[] = $p->checkin_time ? Carbon::parse($p->checkin_time)->format('H:i') : '';
                    $row[] = $p->checkout_time ? Carbon::parse($p->checkout_time)->format('H:i') : '';
                    $row[] = ($p->duration && $p->duration != '00:00:00')
                        ? Carbon::parse($p->duration)->format('H:i')
                        : '';

                    $status = ($p->status === 'A' && $p->duration !== '00:00:00')
                        ? 'P'
                        : ($p->status ?? '');

                    $row[] = $status;

                    if (in_array($status, ['A', 'Absent'], true)) {
                        $weeklyLeaveCount++;
                    }
                } else {
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                    $row[] = 'A';
                    $weeklyLeaveCount++;
                }
            }

            $row[] = $weeklyLeaveCount;
            $row[] = $totalLeaveCount;
            $data[] = $row;
        }

        $data[] = ['']; // spacing between teams
    }

    return $data;
}
public function styles(Worksheet $sheet)
{
    $highestRow    = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    // === HEADER STYLING (UNCHANGED) ===
    for ($row = 1; $row <= $highestRow; $row++) {

        $val = $sheet->getCell("A{$row}")->getValue();

        if (str_contains($val, 'Attendance of FORTIGRID')) {

            $sheet->mergeCells("A{$row}:{$highestColumn}{$row}");

            $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E1F2']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            // Week header
            $weekdayRow = $row + 1;
            $colIndex = 2;

            foreach ($this->weekDates as $day) {

                $startCol = Coordinate::stringFromColumnIndex($colIndex);
                $endCol   = Coordinate::stringFromColumnIndex($colIndex + 3);

                $sheet->mergeCells("{$startCol}{$weekdayRow}:{$endCol}{$weekdayRow}");

                $sheet->getStyle("{$startCol}{$weekdayRow}:{$endCol}{$weekdayRow}")
                    ->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'CAEEFB']
                        ],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'font' => ['bold' => true]
                    ]);

                $colIndex += 4;
            }

            // Employee Name Header
            $employeeHeaderRow = $row + 1;
            $sheet->getStyle("A{$employeeHeaderRow}")
                ->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'CAEEFB']
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'font' => ['bold' => true]
                ]);

            // Sub header
            $subHeaderRow = $row + 2;
            $colIndex = 2;

            foreach ($this->weekDates as $day) {
                for ($i = 0; $i < 4; $i++) {

                    $col = Coordinate::stringFromColumnIndex($colIndex + $i);

                    $sheet->getStyle("{$col}{$subHeaderRow}")
                        ->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'D9D9D9']
                            ],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            'font' => ['bold' => true]
                        ]);
                }
                $colIndex += 4;
            }

            // Week Days Leave and Total days leave columns header styling
            $weekdaysLeaveCol = Coordinate::stringFromColumnIndex($colIndex);
            $totalLeavesCol = Coordinate::stringFromColumnIndex($colIndex + 1);

            // Week Days Leave header
            $sheet->getStyle("{$weekdaysLeaveCol}{$weekdayRow}:{$weekdaysLeaveCol}{$subHeaderRow}")
                ->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'CAEEFB']
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'font' => ['bold' => true]
                ]);

            // Total days leave header
            $sheet->getStyle("{$totalLeavesCol}{$weekdayRow}:{$totalLeavesCol}{$subHeaderRow}")
                ->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'CAEEFB']
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'font' => ['bold' => true]
                ]);
        }
    }

    // ===  STATUS COLOR (RESTORED CORRECTLY) ===
    foreach ($this->weekDates as $index => $day) {

        $statusColIndex = 2 + ($index * 4) + 3;
        $col = Coordinate::stringFromColumnIndex($statusColIndex);

        for ($row = 4; $row <= $highestRow; $row++) {

            $cell = "{$col}{$row}";
            $val  = $sheet->getCell($cell)->getValue();

            //  Apply only known statuses
            if (isset($this->statusColors[$val])) {

                // remove text (as per your requirement)
                $sheet->setCellValue($cell, '');

                $sheet->getStyle($cell)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $this->statusColors[$val]],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER
                    ]
                ]);
            }
        }
    }

    // ===  TEAM-WISE HOLIDAY MERGE (WITH COLOR) ===
    $teamStart = null;

    for ($row = 1; $row <= $highestRow; $row++) {

        $val = $sheet->getCell("A{$row}")->getValue();

        if (str_contains($val, 'Attendance of FORTIGRID')) {

            if ($teamStart !== null) {
                $this->mergeHolidayBlock($sheet, $teamStart, $row - 2);
            }

            $teamStart = $row + 3;
        }
    }

    if ($teamStart !== null) {
        $this->mergeHolidayBlock($sheet, $teamStart, $highestRow);
    }

    // === BORDERS (RESTORED) ===
    $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
        ->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);


    // === LEGEND (RESTORED) ===
$legendStartRow = $highestRow + 3;

$sheet->setCellValue("A{$legendStartRow}", "Status:");
$sheet->getStyle("A{$legendStartRow}")->getFont()->setBold(true)->setSize(12);

$legendRow = $legendStartRow + 1;

// Your original + holiday
$legendColors = [
    'Work From Office'      => 'b4e5a2',
    'Work From Home'        => 'd86ecc',
    '1/2 Day'               => 'f2aa84',
    'Present not Punching'  => 'ffff00',
    'Absent'                => 'F52727',
    'Holiday'               => '9BC2E6', 
];

foreach ($legendColors as $label => $color) {

    // Color box
    $sheet->setCellValue("A{$legendRow}", '');
    $sheet->getStyle("A{$legendRow}")->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $color],
        ],
    ]);

    // Text
    $sheet->setCellValue("B{$legendRow}", $label);
    $sheet->mergeCells("B{$legendRow}:D{$legendRow}");

    $sheet->getStyle("B{$legendRow}")->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
    ]);

    $legendRow++;
}
    return [];
}
    public function columnWidths(): array
{
    $widths = [
        'A' => 15,
    ];

    // Calculate last two columns for "Week Days Leave" and "Total days leave"
    $lastColumnIndex = 2 + (count($this->weekDates) * 4);
    $weekDaysLeaveCol = Coordinate::stringFromColumnIndex($lastColumnIndex);
    $totalDaysLeaveCol = Coordinate::stringFromColumnIndex($lastColumnIndex + 1);

    $widths[$weekDaysLeaveCol] = 18;
    $widths[$totalDaysLeaveCol] = 18;

    return $widths;
}
private function mergeHolidayBlock($sheet, $startRow, $endRow)
{
    foreach ($this->weekDates as $index => $day) {

        $statusColIndex = 2 + ($index * 4) + 3;

        $inCol     = Coordinate::stringFromColumnIndex($statusColIndex - 3);
        $statusCol = Coordinate::stringFromColumnIndex($statusColIndex);

        $start = null;
        $end   = null;
        $name  = null;

        for ($row = $startRow; $row <= $endRow; $row++) {

            $val = $sheet->getCell("{$statusCol}{$row}")->getValue();

            if (!empty($val) && !isset($this->statusColors[$val])) {

                if ($start === null) {
                    $start = $row;
                    $name  = $val;
                }

                $end = $row;

            } else {

                if ($start !== null) {

                    $range = "{$inCol}{$start}:{$statusCol}{$end}";
                    $sheet->mergeCells($range);

                    // show holiday name
                    $sheet->setCellValue("{$inCol}{$start}", $name);

                    $sheet->getStyle($range)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'DEE6EF'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => true
                        ],
                        'font' => [
                            'bold' => true,
                            'size' => 12
                        ]
                    ]);

                    $start = null;
                    $end   = null;
                    $name  = null;
                }
            }
        }

        // last block
        if ($start !== null) {

            $range = "{$inCol}{$start}:{$statusCol}{$end}";
            $sheet->mergeCells($range);

            $sheet->setCellValue("{$inCol}{$start}", $name);

            $sheet->getStyle($range)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DEE6EF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true
                ],
                'font' => [
                    'bold' => true,
                    'size' => 16
                ]
            ]);
        }
    }
}

}