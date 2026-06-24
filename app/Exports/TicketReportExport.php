<?php

namespace App\Exports;

use App\Models\RaiseTicket;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TicketReportExport implements FromView
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $query = RaiseTicket::with(['AssignedTicketMembers', 'ticketType', 'problemType', 'ticketStatus', 'TicketOwner']);

        if ($this->filters['ticket_status']) {
            $query->where('ticket_status', $this->filters['ticket_status']);
        }
        if ($this->filters['start_date'] && $this->filters['end_date']) {
            $start = \Carbon\Carbon::createFromFormat('d-m-Y', $this->filters['start_date'])->startOfDay();
            $end = \Carbon\Carbon::createFromFormat('d-m-Y', $this->filters['end_date'])->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }
           $teamMemId =$this->filters['members_id'];
        if ($teamMemId) {
            $ids = is_array($teamMemId) ? $teamMemId : explode(',', $teamMemId);
            $query->whereHas('AssignedTicketMembers', function ($query1) use ($ids) {
                $query1->whereIn('assign_mem_id', $ids);
            });
        }
        $tickets = $query->get();
        return view('exports.ticket_report_excel', compact('tickets'));
    }
}