<?php
namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;


class ExpenseExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    protected $filters;
    protected $rowCount;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $transactions = Transaction::with('items.expenseItem')->where('is_deleted', false)
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
            ->get();

        $data = [];
        foreach ($transactions as $transaction) {
            $credit = ($transaction->transaction_type === 'credit' ||  $transaction->transaction_type === 'reversal')? $transaction->amount : 0;
            $debit = $transaction->transaction_type === 'debit' ? $transaction->amount : 0;
            $balance = $transaction->available_amt;
            $payment_type=$transaction->payment_type;
            if($transaction->transaction_type === 'debit'){
                    $expenseTypes = '';
                    $expenseTypes = implode(PHP_EOL, $transaction->items->map(function ($item) {
                            return '   ' . ($item->expenseItem->expense_type_name ?? 'N/A');
                        })->toArray());
            } else if($transaction->transaction_type === 'credit'){
                $expenseTypes = '   Credit';
            } else if($transaction->transaction_type === 'reversal'){
                $expenseTypes = '   Reversal';
            } else{
                 $expenseTypes = '';
               $expenseTypes = implode(PHP_EOL, $transaction->items->map(function ($item) {
                            return '  ' . ($item->expenseItem->expense_type_name ?? 'N/A');
                        })->toArray());
            }

            $data[] = [
                Carbon::parse($transaction->transaction_date)->format('d-M Y'),
                $expenseTypes,
                $credit,
                $debit,
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
            ['DATE', 'EXPENSES', 'CREDIT', 'DEBIT','BALANCE']
        ];
    }

  public function styles(Worksheet $sheet)
{
    $totalRows = 2 + $this->rowCount; // 1 title + 1 header + data rows
    $range = "A1:E{$totalRows}";
    $sheet->mergeCells('A1:E1');

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
    $sheet->getStyle('A1:E1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'D9F2D0'],
        ],
    ]);

    $sheet->getStyle('A2:E2')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FBE3D6'],
        ],
    ]);

    if ($this->rowCount > 0) {
         $sheet->getStyle("B3:B{$totalRows}")
        ->getAlignment()
        ->setHorizontal('left');
        $sheet->getStyle("A3:E{$totalRows}")->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DEE6EF'],
            ],
        ]);
    }

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
        return 'Expense Report';
    }
}