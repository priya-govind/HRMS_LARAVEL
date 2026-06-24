<?php

namespace App\Exports;

use App\Models\Inventory;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InventoryReportExport implements FromView
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = Inventory::with('assignments');

        if ($this->filters['inventory_type']) {
            $query->where('asset_type', $this->filters['ticket_status']);
        }
        if ($this->filters['brand']) {
            $query->where('asset_brand', $this->filters['brand']);
        }
        if ($this->filters['user_id']) {
            $user_id=$this->filters['user_id'];
            $query->whereHas('assignments.employee', function ($subQuery) use ($user_id) {
                $subQuery->where('id', $user_id);
            });
        }
        
        $tickets = $query->get();
        return view('exports.inventory_report_excel', compact('tickets'));
    }
}