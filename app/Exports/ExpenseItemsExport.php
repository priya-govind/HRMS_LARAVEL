<?php 
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;

class ExpenseItemsExport implements FromArray, WithHeadings, WithEvents

{
    protected $fromDate;
    protected $toDate;
    protected $filters;
    protected $rowCount;

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

        if ($this->filters['start_date'] || $this->filters['end_date']) {
            $title .= " (" . ($this->filters['start_date'] ?? '...') . " to " . ($this->filters['end_date'] ?? '...') . ")";
        }

        $rows[] = [$title];
        $rows[] = []; // spacer

        $expenseItems = DB::table('expense_items')->get();

        foreach ($expenseItems as $item) {
            $rows[] = [$item->expense_type_name]; // subtitle
            $rows[] = ['Transaction Date', 'Amount'];

            $query = DB::table('monthly_expense_items as mei')
                ->join('monthly_expense as me', 'mei.expense_id', '=', 'me.id')
                ->select('me.transaction_date', 'mei.exp_amount')
                ->where('mei.expense_item_id', $item->id);

            if ($this->filters['start_date']) {
                $query->whereDate('me.transaction_date', '>=', $this->filters['start_date']);
            }

            if ($this->filters['end_date']) {
                $query->whereDate('me.transaction_date', '<=', $this->filters['end_date']);
            }

            $data = $query->orderBy('me.transaction_date')->get();

            foreach ($data as $entry) {
                $rows[] = [$entry->transaction_date, $entry->exp_amount];
            }

            $total = $data->sum('exp_amount');
            $rows[] = ['Total', $total];
            $rows[] = []; // spacer
        }

        return $rows;
    }

    public function headings(): array
    {
        return []; // headings are manually added in array()
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A')->getAlignment()->setWrapText(true);
            },
        ];
    }
}