<?php

namespace App\Imports;

use App\Models\BirthdayCalendar;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BirthdayImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     */
public function model(array $row)
{
    try {
        $row = array_change_key_case($row, CASE_LOWER);
        $employeeCode = trim($row['code'] ?? null);
        $employeeName = trim($row['employee_name'] ?? '');
        $birthday = trim($row['birthday'] ?? '');

        if (empty($employeeCode)) {
            throw new \Exception("Missing employee code in row: " . json_encode($row));
        }

        if (!empty($birthday)) {
            if (is_numeric($birthday)) {
                $birthDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($birthday)->format('Y-m-d');
            } else {
                $birthDate = Carbon::parse($birthday)->format('Y-m-d');
            }
        } else {
            $birthDate = null;
        }
    if(!empty($birthDate)){
            return BirthdayCalendar::firstOrCreate(
                ['employee_code' => $employeeCode],
                [
                    'employee_name' => $employeeName,
                    'birth_date' => $birthDate,
                ]
            );
    }

    } catch (\Exception $e) {
        throw new \Exception("Error during import: " . $e->getMessage());
    }
}



}
