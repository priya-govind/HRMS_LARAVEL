<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use App\Models\Holiday;
use Illuminate\Support\Str;
class AttendanceExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $dateRange = [];
    protected $teamTypeId;
    protected $cellStatuses = [];
    public function __construct(string $startDate, string $endDate, $teamTypeId = null)
    {
        $this->teamTypeId = $teamTypeId;
        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            if (!in_array($date->format('l'), ['Saturday', 'Sunday'])) {
                $this->dateRange[] = $date->format('Y-m-d');
            }
        }
    }
    public function array(): array
    {
        $employeesQuery = User::with(['attendances' => function ($q) {
            $q->whereBetween('chkinDate', [
                $this->dateRange[0] . ' 00:00:00',
                end($this->dateRange) . ' 23:59:59',
            ]);
        }]);
        $employeesQuery->whereHas('roles', fn($q) =>
         $q->whereNotIn('roles.id', config('global.role_without_attendance'))
          ->where('roles.id', '!=', session('role_id'))         
         );
          if(in_array(session('role_id'),config('global.task_monitor_roles'))){
                        $employeesQuery->whereHas('roles', function ($q) {
                                $q->where('roles.id','!=',config('global.first_level_role'));
                                 $q->where('roles.id','!=',config('global.task_monitor_roles'));
                        })  ;
            }
        $employees = $employeesQuery->get();
        $data = [];
        $actualRowIndex = 1;
            $startDate_frmt = \DateTime::createFromFormat('Y-m-d', $this->dateRange[0]);
            $year = $startDate_frmt->format('Y');
            $month = $startDate_frmt->format('F');
            $weekOfYear = $startDate_frmt->format('W');
            $title = "{$year} {$month} {$weekOfYear}th Week Attendance of FORTIGRID";
            $data[] = [$title];
            $actualRowIndex++;
            // Day Headers
            $header1 = ['Employee Name'];
            foreach ($this->dateRange as $d) {
                $label = strtoupper(Carbon::parse($d)->format('l dS'));
                $header1 = array_merge($header1, [$label, '', '', '']);
            }
            $header1[] = 'Week Days Leave';
            $data[] = $header1;
            $actualRowIndex++;

            $header2 = [''];
            foreach ($this->dateRange as $d) {
                $header2 = array_merge($header2, ['IN', 'OUT', 'Worked', 'STATUS']);
            }
            $header2[] = '';
            $data[] = $header2;
            $actualRowIndex++;
        foreach($employees as $emp){
                $row = [$emp->name];
                $leaveCount = 0;
                $colIndex = 2;
                $rowStatusData = []; // collect statuses for current row
                foreach ($this->dateRange as $d) {
                    $att = $emp->attendances
                        ->whereBetween('chkinDate', [$d . ' 00:00:00', $d . ' 23:59:59'])
                        ->first();

                    if ($att) {
                         $chkin = Carbon::parse($att->chkinDate);
                        $chkout = $att->chkoutDate
                            ? Carbon::parse($att->chkoutDate)
                            : Carbon::parse($chkin->format('Y-m-d') . ' 18:00'); 

                        $in = date('H:i', strtotime($att->chkinDate));
                        $out = $att->chkoutDate? date('H:i', strtotime($att->chkoutDate)) :  date('H:i', strtotime('18:00')); 
                         $minutesWorked = $chkin->diffInMinutes($chkout);
                        $worked = intdiv($minutesWorked, 60) . 'h ' . ($minutesWorked % 60) . 'm';
                        $actualStatus = $att->workingMode->work_mode_name ?? 'Unknown';
                        $status = '';
                    } else {
                        $in = $out = $worked = '';
                        $actualStatus = 'Absent';
                        $status = '';
                        $leaveCount++;
                    }
                    $row = array_merge($row, [$in, $out, $worked, $status]);

                    $rowStatusData[] = [
                        'row' => $actualRowIndex, // Corrected here: use current row index before increment
                        'col' => $colIndex + 3,
                        'status' => $actualStatus,
                    ];
                    $colIndex += 4;
                }
                $row[] = $leaveCount === count($this->dateRange) ? 'Absent' : ($leaveCount > 0 ? "{$leaveCount} Days" : 'Present');
                $data[] = $row;
                foreach ($rowStatusData as $statusInfo) {
                    $this->cellStatuses[] = $statusInfo;
                }
                $actualRowIndex++;
        }
        return $data;
    }
    public function headings(): array
    {
        return [];
    }
    public function styles(Worksheet $sheet)
{
    $sheet->getDefaultColumnDimension()->setWidth(8);
    $highestCol = $sheet->getHighestColumn();
    $highestRow = $sheet->getHighestRow();

    $statusColors = [
        'Work From Office'      => 'b4e5a2',
        'Work From Home'        => 'd86ecc',
        '1/2 Day'               => 'f2aa84',
        'Present not Punching'  => 'ffff00',
        'Absent'                => 'F52727',
        'Unknown'               => '7F7F7F',
    ];

    for ($r = 1; $r <= $highestRow; $r++) {
        $val = $sheet->getCell("A{$r}")->getValue();
        if (is_string($val) && str_contains($val, 'Attendance of FORTIGRID')) {
            // Merge title row
            $sheet->mergeCells("A{$r}:{$highestCol}{$r}");
            $sheet->getStyle("A{$r}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0a0202']],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
				],											   
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'd9e1f2'],
                ],
            ]);

            // Header rows
            $headerRow1 = $r + 1; // row with MONDAY 02ND, etc.
            $headerRow2 = $r + 2; // row with IN/OUT/Worked/STATUS

            $colIndex = 2;
            foreach ($this->dateRange as $d) {
                $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $endCol   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 3);

                // Merge the 4 cells into one block
                $sheet->mergeCells("{$startCol}{$headerRow1}:{$endCol}{$headerRow1}");

                // Center the label across merged block
                $sheet->getStyle("{$startCol}{$headerRow1}")
                      ->getAlignment()
                      ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                      ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $colIndex += 4;
            }

            // Style header rows
            $sheet->getStyle("A{$headerRow1}:{$highestCol}{$headerRow2}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '0a0202']],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,																				   
                ],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'caeefb'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color'       => ['argb' => '000000'],
                    ],
                ],
            ]);
        }
    }

    // Apply status colors
    foreach ($this->cellStatuses as $item) {
        $color = $statusColors[$item['status']] ?? '0a0202';
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($item['col']);
        $cellRef = "{$colLetter}{$item['row']}";
        $sheet->getStyle($cellRef)->applyFromArray([
            'fill' => [
								 
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
														  
                'startColor' => ['rgb' => $color],
            ],
																									  
        ]);
																	   
    }

    // Legend
    $legendStartRow = $highestRow + 3;
    $sheet->setCellValue("A{$legendStartRow}", "Status:");
    $sheet->getStyle("A{$legendStartRow}")->getFont()->setBold(true);
    $legendRow = $legendStartRow + 1;
    foreach ($statusColors as $status => $color) {
        $sheet->setCellValue("A{$legendRow}", '');
        $sheet->getStyle("A{$legendRow}")->applyFromArray([
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => $color],
            ],
            'alignment' => [
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $sheet->setCellValue("B{$legendRow}", $status);
        $sheet->getStyle("B{$legendRow}")->applyFromArray([
            'alignment' => [
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $legendRow++;
    }

    // Borders + freeze
    $sheet->getStyle("A1:{$highestCol}{$legendRow}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color'       => ['argb' => '000000'],
            ],
        ],
        'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
    ]);

    $sheet->freezePane('A4'); // Title + 2 header rows
}

}