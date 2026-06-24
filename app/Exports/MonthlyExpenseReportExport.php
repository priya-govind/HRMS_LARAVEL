<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class MonthlyExpenseReportExport implements FromArray, WithHeadings, WithEvents

{
    protected $filters;
    public function __construct($filters)
    {
        $this->filters = $filters;
    }
    // public function __construct($fromDate = null, $toDate = null)
    // {
    //     $this->filters['start_date'] = $fromDate;
    //     $this->filters['end_date'] = $toDate;
    // }

public function array(): array
    {
        $rows = [];
        $title = "Monthly Expense Report";

        if ( !empty($this->filters['start_date']) || !empty($this->filters['end_date'])) {
            $title .= " (" . ($this->filters['start_date'] ?? '...') . " to " . ($this->filters['end_date'] ?? '...') . ")";
        }

        $rows[] = [$title];
        $rows[] = [];

        $expenseItems = DB::table('expense_items')
            ->when(!empty($this->filters['exp_items']), function ($query) {
                $query->whereIn('id', (array) $this->filters['exp_items']);
            })
            ->get();

        foreach ($expenseItems as $item) {
                $query = DB::table('monthly_expense_items as mei')
                    ->join('monthly_expense as me', 'mei.expense_id', '=', 'me.id')
                    ->select('me.transaction_date', 'mei.exp_amount')
                    ->where('mei.expense_item_id', $item->id)
                     ->where('me.is_deleted', '!=','1');

                if (!empty($this->filters['start_date'])) {
                     $start = Carbon::parse($this->filters['start_date'])->format('Y-m-d');
                    $query->whereDate('me.transaction_date', '>=', $start);
                }

                if (!empty($this->filters['end_date'])) {
                    $end = Carbon::parse($this->filters['end_date'])->format('Y-m-d');
                    $query->whereDate('me.transaction_date', '<=', $end);
                }

                $data = $query->orderBy('me.transaction_date')->get();

                // ✅ Skip if no data
                if ($data->isEmpty()) {
                    continue;
                }

                $rows[] = [$item->expense_type_name];
                $rows[] = ['Transaction Date', 'Amount'];

                foreach ($data as $entry) {
                    $formattedDate = Carbon::parse($entry->transaction_date)->format('jS M Y');
                    $rows[] = [$formattedDate, $entry->exp_amount];

                }

               $total = $data->sum(function ($item) {
                    return (float) $item->exp_amount;
                });

                $rows[] = ['Total', $total];
                $rows[] = [];
        }

        return $rows;
    }

    public function headings(): array
    {
        return []; // headings are manually added in array()
    }

    // public function registerEvents(): array
    // {
    //     return [
    //         AfterSheet::class => function (AfterSheet $event) {
    //             $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    //             $event->sheet->getStyle('A')->getAlignment()->setWrapText(true);
    //         },
    //     ];
    // }
public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet;
            $rowIndex = 1;

            // Title row
            $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}");
            $sheet->setCellValue("A{$rowIndex}", "Expense Report");
            $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle("A{$rowIndex}")->getFill()->setFillType('solid')->getStartColor()->setRGB('FFA500'); // Orange
            $sheet->getStyle("A{$rowIndex}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowIndex++;

            $expenseItems = DB::table('expense_items')
                ->when(!empty($this->filters['exp_items']), function ($query) {
                    $query->whereIn('id', (array) $this->filters['exp_items']);
                })
                ->get();
                $cnt=1;
                $grand_total=array();
            foreach ($expenseItems as $item) {
                // Fetch data for this item
                $query = DB::table('monthly_expense_items as mei')
                    ->join('monthly_expense as me', 'mei.expense_id', '=', 'me.id')
                    ->select('me.transaction_date', 'mei.exp_amount')
                    ->where('mei.expense_item_id', $item->id)
                    ->where('me.is_deleted', '!=','1');

                // if (!empty($this->filters['start_date'])) {
                //     $query->whereDate('me.transaction_date', '>=', $this->filters['start_date']);
                // }

                // if (!empty($this->filters['end_date'])) {
                //     $query->whereDate('me.transaction_date', '<=', $this->filters['end_date']);
                // }
                if (!empty($this->filters['start_date'])) {
                     $start = Carbon::parse($this->filters['start_date'])->format('Y-m-d');
                    $query->whereDate('me.transaction_date', '>=', $start);
                }

                if (!empty($this->filters['end_date'])) {
                    $end = Carbon::parse($this->filters['end_date'])->format('Y-m-d');
                    $query->whereDate('me.transaction_date', '<=', $end);
                }

                $data = $query->orderBy('me.transaction_date')->get();

                if ($data->isEmpty()) {
                    continue;
                }

                // Item name row
                $sheet->setCellValue("A{$rowIndex}", $item->expense_type_name);
                $sheet->mergeCells("A{$rowIndex}:B{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true);
                $sheet->getStyle("A{$rowIndex}")->getFill()->setFillType('solid')->getStartColor()->setRGB('87CEEB'); // Sky blue
                $rowIndex++;

                // Header row
                $sheet->setCellValue("A{$rowIndex}", "Transaction Date");
                $sheet->setCellValue("B{$rowIndex}", "Amount");
                $sheet->getStyle("B{$rowIndex}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A{$rowIndex}:B{$rowIndex}")->getFont()->setBold(true);
                $sheet->getStyle("A{$rowIndex}:B{$rowIndex}")->getFill()->setFillType('solid')->getStartColor()->setRGB('D3D3D3'); // Grey
                $rowIndex++;

                // Data rows
                foreach ($data as $entry) {
                    $formattedDate = Carbon::parse($entry->transaction_date)->format('jS M Y');
                    $sheet->setCellValue("A{$rowIndex}", $formattedDate);
                   // $sheet->setCellValue("A{$rowIndex}", $entry->transaction_date);
                    $sheet->setCellValue("B{$rowIndex}", $entry->exp_amount);
                    $rowIndex++;
                }

                // Total row
                $total = $data->sum(function ($item) {
                    return (float) $item->exp_amount;
                });

                $sheet->setCellValue("A{$rowIndex}", "Total");
                $sheet->setCellValue("B{$rowIndex}", $total);
                //$sheet->mergeCells("A{$rowIndex}:B{$rowIndex}");
                $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true);
                $sheet->getStyle("A{$rowIndex}:B{$rowIndex}")->getFill()->setFillType('solid')->getStartColor()->setRGB('b4e5a2'); 
                $rowIndex++;
$grand_total[]=$total;
                // Spacer row
                //$rowIndex++;
                $cnt++;
            }
            if($cnt>1){
            $all_total=array_sum($grand_total);
            $sheet->setCellValue("A{$rowIndex}", "Grand Total");
                            $sheet->setCellValue("B{$rowIndex}", $all_total);
                            //$sheet->mergeCells("A{$rowIndex}:B{$rowIndex}");
                            $sheet->getStyle("A{$rowIndex}")->getFont()->setBold(true);
                            $sheet->getStyle("A{$rowIndex}:B{$rowIndex}")->getFill()->setFillType('solid')->getStartColor()->setRGB('b4e5a2'); 
                            $rowIndex++;
            }
            // Optional: Auto-size columns
            $sheet->getDelegate()->getColumnDimension('A')->setAutoSize(true);
            $sheet->getDelegate()->getColumnDimension('B')->setAutoSize(true);

             $sheet->getDelegate()->getStyle($sheet->getDelegate()->calculateWorksheetDimension())
                                 ->getFont()->setSize(21);

        },
    ];
}

}