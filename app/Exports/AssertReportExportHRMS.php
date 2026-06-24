<?php

namespace App\Exports;

use App\Models\AssetItems;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AssertReportExportHRMS implements FromView
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = AssetItems::with('assignments','ItemConfigurationValues');

        if (isset($this->filters['item_type'])) {
            $query->where('item_type', $this->filters['item_type']);
        }
         if (isset($this->filters['item_category'])) {
            $query->where('item_category', $this->filters['item_category']);
        }
        if (isset($this->filters['brand'])) {
            $query->where('item_brand', $this->filters['brand']);
        }
        if (isset($this->filters['user_id'])) {
            $user_id=$this->filters['user_id'];
            $query->whereHas('assignments.employee', function ($subQuery) use ($user_id) {
                $subQuery->where('id', $user_id);
            });
        }
        $attrIds=$this->filters['search_configure_attribute'];
        if(isset($attrIds)){
                $query->whereHas('ItemConfigurationValues', function ($subQuery) use ($attrIds) {
                        $subQuery->whereIn('option_id', $attrIds);
                    });
            }
        $tickets = $query->get();
        return view('exports.asset_report_excel_hrms', compact('tickets'));
    }
}