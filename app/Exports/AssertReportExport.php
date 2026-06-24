<?php

namespace App\Exports;

use App\Models\AssetItems;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AssertReportExport implements FromView
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = AssetItems::with('assignments');

        if ($this->filters['item_type']) {
            $query->where('item_type', $this->filters['item_type']);
        }
         if ($this->filters['item_category']) {
            $query->where('item_category', $this->filters['item_category']);
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
        return view('exports.asset_report_excel', compact('tickets'));
    }
}