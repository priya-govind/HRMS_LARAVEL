<?php

namespace App\Imports;

use App\Models\PunchAttendance;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AttendanceImport implements ToCollection
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    protected $workMode;
    protected $teamType;
    protected $month;

    public function __construct($workMode,$teamType,$month)
    {
        $this->workMode = $workMode;
        $this->teamType = $teamType;
        $this->month= $month;
    }

   public function collection(Collection $rows)
    {
        if($this->workMode==1){
            $coolecti=$this->punch_input($rows,$this->month);
        } else {
            $coolecti=$this->manual_entry($rows);
        }
       
    }
    public function manual_entry(Collection $rows){
        $working = $rows->slice(1)->values(); 
            foreach ($working as $row) {
                if (empty($row[0]) && $row[1]) {
                    continue;
                }
                $employeeName = ucwords(strtolower(trim(preg_replace('/\s+/', ' ', $row[0] ?? ''))));
                $emp_code = $row[1] ?? null;
                $punch_date = $row[2] ?? null;
                $in_time = $row[3] ?? '00:00';
                $out_time = $row[4] ?? '00:00';
                $status = $row[5] ?? 'A';
                /** Added to Check Status where checkin present and Status marked Absent Scenario */
                if((!empty($row[5]) && $row[5]=='A') &&  $row[3]!='00:00' && $row[4]!='00:00'){
                    $status = 'P';
                }

                if ($row->filter()->isEmpty()) {
                    continue; 
                }

                if (empty($emp_code) || empty($punch_date)) {
                    Log::warning('Missing critical data', ['row' => $row]);
                    continue;
                }

                try {
                    $formattedDate = Carbon::parse($punch_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::error('Invalid punch_date format', ['value' => $punch_date, 'error' => $e->getMessage()]);
                    continue;
                }

                Log::info('Chunk data', [
                    'employee_name' => $employeeName,
                    'employee_code' => $emp_code,
                    'checkin_time' => $in_time,
                    'checkout_time' => $out_time,
                    'punch_date' => $formattedDate,
                    'status' => $status,
                ]);

                try {
                    PunchAttendance::updateOrCreate(
                        [
                            'employee_code' => $emp_code,
                            'punch_date' => $formattedDate,
                        ],
                        [
                            'employee_name' => $employeeName,
                            'checkin_time' => $in_time,
                            'checkout_time' => $out_time,
                            'status' => $status,
                            'team_type' => $this->teamType,
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error('Insert failed', ['error' => $e->getMessage()]);
                }
            }
    }
  public function punch_input(Collection $rows)
{
    $monthName = $this->month;
    $month = date("n", strtotime($monthName));
    $year = Carbon::now()->year;
    //$year='2024';
    //  Safely access and sanitize header row
    if (!isset($rows[12]) || !$rows[12] instanceof Collection) {
        Log::warning("Header row at index 12 is missing or malformed. Skipping punch import.");
        return; //  Exit early if header is missing
    }

    $header = $rows[12]->map(fn($val) => is_string($val) ? trim(preg_replace('/\s+/', ' ', $val)) : null);

    // Extract date columns dynamically
    $dateColumns = [];
    foreach ($header as $index => $value) {
        if (preg_match('/^\d{1,2}\s(M|T|W|Th|F|St|S)$/', $value)) {
            [$date, $day] = explode(' ', $value);
            $dateColumns[] = [
                'index' => $index,
                'label' => $value,
                'date' => (int) $date,
                'day' => $day,
            ];
        }
    }

    $available_dates = count($dateColumns);
    $working = $rows->slice(13)->values();

    for ($rowIndex = 0; $rowIndex < $working->count(); $rowIndex += 4) {
        $chunk = $working->slice($rowIndex, 4)->values();
        if ($chunk->count() < 4) break;

        $emp_name = trim(str_replace('Name:', '', $chunk[0][3]));
        $employeeName = ucwords(strtolower(trim(preg_replace('/\s+/', ' ', $emp_name))));
        $emp_code = trim(str_replace('Code:', '', $chunk[1][3]));

        $intime = $chunk[1];
        $outtime = $chunk[2];
        $status = $chunk[3];
        $duration = $chunk[0];

        $start = $dateColumns[0]['index'];

        for ($i = 0; $i < $available_dates;) {
            $day = $dateColumns[$i]['day'];
            $punchDate = Carbon::create($year, $month, $dateColumns[$i]['date'])->toDateString();
            $statusValue = $status[$start] ?? null;

            $isWorkingDay = !in_array($day, ['St', 'S']);
            $isValidStatus = $statusValue && $statusValue !== 'WO';

            if ($isWorkingDay && $isValidStatus) {
                $checkin = $this->parseExcelTime($intime[$start] ?? null);
                $checkout = $this->parseExcelTime($outtime[$start] ?? null);
               // $worked_time = $this->parseExcelDuration($duration[$start] ?? null);
               
                $current_status = ($checkin && $checkout && $checkin !== '00:00:00' && $checkout !== '00:00:00')
                    ? $statusValue
                    : ($statusValue && $statusValue !== '00:00:00' ? $statusValue : 'A');
                $worked_time = $this->parseExcelDuration($duration[$start] ?? null);

                if ($worked_time) {
                    // $worked_time is already "HH:MM:SS" from parseExcelDuration
                    [$h, $m, $s] = explode(':', $worked_time);
                    $worked_hrs = (int)$h + ((int)$m / 60) + ((int)$s / 3600);
                } else {
                    $worked_time = '00:00:00';
                    $worked_hrs = 0;
                }

                 // Attendance status logic based on numeric hours
                 if ($worked_hrs >= 4) {
                    $attn_status = 'P';
                } elseif ($worked_hrs < 4 && $worked_hrs >= 3) {
                    $attn_status = '½P';
                } elseif ($worked_hrs < 2) {
                    $attn_status = 'A';
                } else {
                    $attn_status = $current_status;
                }

                // Attendance status logic based on numeric hours
                // if ($worked_hrs > =4) {
                //     $attn_status = 'P';
                // } elseif ($worked_hrs <) {
                //     $attn_status = '½P';
                // } elseif ($worked_hrs < 2) {
                //     $attn_status = 'A';
                // } else {
                //     $attn_status = $current_status;
                // }

                $data = [
                    'employee_code' => $emp_code,
                    'checkin_time'  => $checkin,
                    'checkout_time' => $checkout,
                    'status'        => $attn_status,
                    'duration'      => $worked_time,   // <-- stored as "HH:MM:SS"
                    'punch_date'    => $punchDate,
                    'employee_name' => $employeeName,
                    'team_type'     => $this->teamType,
                ];

                Log::info('Punch record', $data);

                $existing = DB::table('punch_attendance')
                    ->where('employee_code', $emp_code)
                    ->whereDate('punch_date', $punchDate)
                    ->first();
                    if ($existing) {
                        DB::table('punch_attendance')
                            ->where('id', $existing->id)
                            ->update($data);

                        Log::info("Updating record", [
                            'id' => $existing->id,
                            'data' => $data
                        ]);
                    } else {
                        try {
                            $insertedId = DB::table('punch_attendance')->insertGetId($data);
                            Log::info("Insert successful", [
                                'id' => $insertedId,
                                'data' => $data
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Insert failed", [
                                'error' => $e->getMessage(),
                                'data' => $data
                            ]);
                        }
                    }
            }

            if (!empty($status[$start])) {
                $i++;
            }
            $start++;
        }
    }

    /** Adding Default Work from Home Members based on the available dates 
 * Note: For Development Team Alone
*/
if($this->teamType=='Development'){
    $wfh_emp=array(1 => 'NOWSATH',2 => 'SASIKALA');
        for($entry=0;$entry<$available_dates ;$entry++){
            $punchDate = Carbon::create($year, $month, $dateColumns[$entry]['date'])->toDateString();
                    foreach($wfh_emp as $emp_code => $emp_name){
                        $employeeName=ucwords(strtolower(trim(preg_replace('/\s+/', ' ', $emp_name))));
                            $data = [
                                    'employee_code' => $emp_code,
                                    'checkin_time' => null,
                                    'checkout_time' => null,
                                    'status' =>'WFH',
                                    'duration' => null,
                                    'punch_date' => $punchDate,
                                    'employee_name' => $employeeName,
                                    'team_type' => $this->teamType,
                                ];

                                Log::info('Punch record', $data);

                                $existing = DB::table('punch_attendance')
                                    ->where('employee_code', $emp_code)
                                    ->whereDate('punch_date', $punchDate)
                                    ->first();

                                if ($existing) {
                                    DB::table('punch_attendance')->where('id', $existing->id)->update($data);
                                } else {
                                    DB::table('punch_attendance')->insert($data);
                                }
                    }   
        }
}

/** Adding Default Work from Home Members based on the available dates 
 * Ends Here
*/
}
public function parseExcelDuration($value)
{
    // Handle string formats like "9:01" or "09:01:00"
    if (is_string($value) && preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
        try {
            $time = Carbon::parse($value);
            return $time->format('H:i:s'); // Always normalize to H:i:s
        } catch (\Exception $e) {
            return null;
        }
    }

    // Handle Excel float (fraction of a day)
    if (is_numeric($value)) {
        $seconds = round($value * 86400); // 86400 seconds in a day
        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs    = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    return null;
}

public function parseExcelTime($value)
{
    // Handle string formats like "9:01" or "09:01:00"
    if (is_string($value) && preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
        try {
            $time = Carbon::parse($value);
            return $time->format('H:i:s'); // Always normalize to H:i:s
        } catch (\Exception $e) {
            return null;
        }
    }

    // Handle Excel float (fraction of a day)
    if (is_numeric($value)) {
        $seconds = round($value * 86400); // 86400 seconds in a day
        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs    = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    return null;
}
}
