<?php

namespace App\Exports;

use App\Models\MonthlyExpense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;


class MonthlyExpenseExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
  protected $filters;
    protected $rowCount;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $transactions = MonthlyExpense::with('items.expenseItem')->where('is_deleted', false)
            ->when(!empty($this->filters['start_date']) && !empty($this->filters['end_date']), function ($query) {
                $query->whereBetween('transaction_date', [
                    Carbon::parse($this->filters['start_date'])->format('Y-m-d'),
                    Carbon::parse($this->filters['end_date'])->format('Y-m-d')
                ]);
            })
            ->when(empty($this->filters['start_date']) || empty($this->filters['end_date']), function ($query) {
                $query->whereMonth('transaction_date', Carbon::now()->month)
                      ->whereYear('transaction_date', Carbon::now()->year);
            })
            ->when(!empty($this->filters['exp_type']), function ($query) {
                $query->where('transaction_type', $this->filters['exp_type']);
            })
            ->when(!empty($this->filters['exp_items']), function ($query) {
                    $query->whereHas('items.expenseItem', function ($subQuery) {
                        $selectedItems = is_array($this->filters['exp_items'])
                            ? $this->filters['exp_items']
                            : explode(',', $this->filters['exp_items']);
                        $subQuery->whereIn('id', $selectedItems);
                    });
                })
            ->get();

        $data = [];
        foreach ($transactions as $transaction) {
            $credit = ($transaction->transaction_type === 'credit' ||  $transaction->transaction_type === 'reversal')? $transaction->trans_amount : 0;
            $debit = $transaction->transaction_type === 'debit' ? $transaction->trans_amount : 0;
            $balance = $transaction->available_amt;
            $payment_type=$transaction->payment_type;
            if($transaction->transaction_type === 'debit'){
                    $expenseTypes = '';
                    $expenseTypes = implode(PHP_EOL, $transaction->items->map(function ($item) {
                            return '• ' . ($item->expenseItem->expense_type_name.' - ₹'.$item->exp_amount ?? 'N/A');
                        })->toArray());
            } else if($transaction->transaction_type === 'credit'){
                $expenseTypes = 'Credit';
            } else{
               $expenseTypes = 'Reversal'; 
            }

            $data[] = [
                Carbon::parse($transaction->transaction_date)->format('d-M Y'),
                $expenseTypes,
                $credit,
                $debit,
                $payment_type,
                $balance
            ];
        }
        $this->rowCount = count($data);
        return collect($data);
    }

    public function headings(): array
    {
        return [
            ['FORTIGRID ICT OFFICE EXPENSES'],
            ['DATE', 'EXPENSES', 'CREDIT', 'DEBIT','PAYMENT TYPE','BALANCE']
        ];
    }

  public function styles(Worksheet $sheet)
{
    $totalRows = 2 + $this->rowCount; // 1 title + 1 header + data rows
    $range = "A1:F{$totalRows}";
    $sheet->mergeCells('A1:F1');

    $sheet->getStyle("B3:B{$totalRows}")
        ->getAlignment()
        ->setHorizontal('center')
        ->setWrapText(true);
    $sheet->getStyle($range)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]) ->getAlignment()
        ->setHorizontal('center');

    // Optional: keep your existing styling for title and header
    $sheet->getStyle('A1:F1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'D9F2D0'],
        ],
    ]);

    $sheet->getStyle('A2:F2')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FBE3D6'],
        ],
    ]);

    return [];
}

    public function columnWidths(): array
    {
        return [
            'A' =>15,
            'B' => 30,
            'E' => 15,
        ];
    }

    public function title(): string
    {
        return 'Monthly Expense Report';
    }
}