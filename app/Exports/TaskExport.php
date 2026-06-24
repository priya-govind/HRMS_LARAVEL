<?php
namespace App\Exports;

use App\Models\PMTasksAssign;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Carbon;

class TaskExport implements FromView
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $userId = session('user_id');
        $statusType = $this->filters['status_type'] ?? null;
        $empId = $this->filters['emp_id'] ?? null;
        $projectId = $this->filters['project_id'] ?? null;
        $moduleId = $this->filters['module_id'];
        $startdate = !empty($this->filters['start_date'])
            ? Carbon::createFromFormat('d-m-Y', $this->filters['start_date'])->format('Y-m-d')
            : null;

        $enddate = !empty($this->filters['end_date'])
            ? Carbon::createFromFormat('d-m-Y', $this->filters['end_date'])->format('Y-m-d')
            : null;

        $query = PMTasksAssign::with(['employee','task.project','task.modules'])
            ->select('pm_task_assign_emp.*');

        if ($statusType == 1) {
            $query->where('employee_id', $userId);
        } elseif ($statusType == 2 && $empId) {
            $query->where('employee_id', $empId);
        } 
        // elseif ($statusType == 2) {
        //     $query->where('employee_id', '!=', $userId);
        // }

        $query->whereHas('task', function ($q) use ($projectId,$moduleId, $startdate, $enddate) {
           if ($projectId) {
                $q->where('project_id', $projectId);
            }
            if ($moduleId) {
                $q->where('module_id', $moduleId);
            }
            if ($startdate && $enddate) {
                 $q->whereBetween('endDate', [$startdate, $enddate]);
            }
        });

        return view('exports.tasks', [
            'tasks' => $query->get()
        ]);
    }
}
