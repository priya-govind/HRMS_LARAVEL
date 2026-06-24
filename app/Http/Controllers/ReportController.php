<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use App\Models\PMTasksAssign;
use App\Models\Projects;
use App\Models\TeamType;
use App\Models\TeamMembers;
use App\Models\ProjectStatus;
use App\Models\RaiseTicket;
use App\Models\ItemType;
use App\Models\Brands;
use App\Models\Inventory;
use App\Models\AssetTypes;
use App\Models\AssetItems;
use App\Models\ComponentTypes;
use App\Models\SoftwareLicenses;
use App\Exports\TicketReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\AssertReportExport;
use App\Exports\AssertReportExportHRMS;
use App\Exports\TaskExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\CarbonPeriod; 

class ReportController extends Controller
{
    public function attendance_report(){
    if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
        return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
    }
    // Load team types
    //$teamTypes = TeamType::whereNull('deleted_at')->get();
     $LoadDateTimepicker=true;
    // Pass it to the view
    return view('reports.attendance_report', compact('LoadDateTimepicker'));
    }
    
    public function tasks_report(){
    if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
        return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
    }
    if(in_array(session('role_id'), config('global.task_monitor_roles'))){
         $status['self']="checked";
         $status['other']='';    
    } else {
         $status['self']="";
         $status['other']="checked";
    }
    $team_ids = session('team_id');
    $LoadDateTimepicker = true;
    $LoadDatatables = true;
    $emp_query = User::with('roles')
        ->where('emp_status', config('global.active_status'))
        ->where('id', '!=', config('global.superadmin_id'))
        ->where('id', '!=', session('user_id'))
        ->whereHas('roles', function($q){
            $q->whereNotIn('roles.id', config('global.restriction_free_roles'));
        });
    // if (in_array(session('role_id'), config('global.task_approve_roles'))) {
    //     $emp_query->whereHas('team_members', function ($query) use ($team_ids) {
    //         $query->whereIn('team_id', $team_ids);
    //     });
    // }
    $employees = $emp_query->get();
    $projects = Projects::get();
    return view('reports.tasks_report', compact('LoadDateTimepicker', 'LoadDatatables','employees', 'projects','status'));
}

public function tasks_report_action(Request $request){
   $userId = session('user_id');
    $statusType = $request->input('status_type');
    $empId = $request->input('emp_id');
    $projectId = $request->input('project_id');
    $moduleId = $request->input('module_id');
    // Safely parse dates with format check
    $startdate = $request->filled('start_date')
        ? Carbon::createFromFormat('d-m-Y', $request->input('start_date'))->format('Y-m-d')
        : null;
    $enddate = $request->filled('end_date')
        ? Carbon::createFromFormat('d-m-Y', $request->input('end_date'))->format('Y-m-d')
        : null;
        //,'task.task_user'
    $query = PMTasksAssign::with(['employee','task.project'])
        ->select('pm_task_assign_emp.*');
        // ->whereHas('task', function ($q) {
        //     $q->whereNull('deleted_at'); // exclude soft-deleted tasks
        // });
    // Filter by employee status
    if ($statusType == 1) {
        $query->where('employee_id', $userId);
    } elseif ($statusType == 2 && $empId) {
        $query->where('employee_id', $empId);
    }
     elseif ($statusType == 2) {
        $query->where('employee_id', '!=', $userId);
    }
        $query->whereHas('task', function ($q) use ($request, $startdate, $enddate) {
             $projectId = $request->input('project_id');
             $moduleId = $request->input('module_id');
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
    return DataTables::of($query)
     ->addIndexColumn()
        ->addColumn('over_all_task', fn($row) => $row->task?->task_name ?? 'N/A')
        ->addColumn('task_end_date', fn($row) => Carbon::parse($row->task?->endDate)->format('d/m/Y h:i A'))
        ->addColumn('proj_name', fn($row) => $row->task->project->proj_name )
        ->addColumn('emp_name', fn($row) => $row->employee->name)
        ->addColumn('overall_status', function ($row) {
            $badge = config('global.task_status_badges')[$row->task?->task_status] ?? ['label' => 'Unknown', 'class' => 'badge-secondary'];
            return '<label class="badge ' . $badge['class'] . '">' . $badge['label'] . '</label>';
        })
        ->addColumn('emp_task_status', function ($row) {
            $badge = config('global.task_status_badges')[$row->emp_task_status] ?? ['label' => 'Unknown', 'class' => 'badge-secondary'];
            return '<label class="badge ' . $badge['class'] . '">' . $badge['label'] . '</label>';
        })
        ->rawColumns(['emp_task_status', 'overall_status'])
        ->make(true);
}
public function exportTasks(Request $request){
    $filters = $request->only([
        'status_type', 'emp_id', 'project_id', 'start_date','module_id', 'end_date'
    ]);
    return Excel::download(new TaskExport($filters), 'task_report.xlsx');
}
public function employees_report(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $LoadDateTimepicker = true;
        $LoadDatatables = true;
            $emp_query=User::with('roles')->where('emp_status',config('global.active_status'))
                        ->where('id','!=', config('global.superadmin_id'))
                         ->where('id','!=', session('user_id'));
        $employees=$emp_query->get(); 
        return view('reports.employees_report',compact('LoadDateTimepicker', 'LoadDatatables','employees'));
    }
    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
       // $teamTypeId = $request->input('team_type_id');
       // $teamTypes = TeamType::whereNull('deleted_at')->get();

            $startDate_frmt = \DateTime::createFromFormat('d-m-Y', $startDate);
            //dd($startDate_frmt);
            $year = $startDate_frmt->format('Y');
            $month = $startDate_frmt->format('F'); // Full month name
            $dayOfMonth = (int)$startDate_frmt->format('j'); // day number in month
            $weekOfYear = ceil($dayOfMonth / 7);            // 1–5 depending on day

            //$weekOfYear = $startDate_frmt->format('W'); // ISO week number

                $period = CarbonPeriod::create($startDate, $endDate);
                $dateRange = [];
                foreach ($period as $date) {
                    $dateRange[] = $date->format('Y-m-d');
                }
               // if(isset($request->team_type_id) && !empty($request->team_type_id)){
                       // $team_ids=$request->team_type_id;
                       // $team_name=TeamType::where('id',$team_ids)->pluck('team_typ_name')->first();  
                        $title= "Attendance Report of FORTIGRID from {$weekOfYear}th Week of $month $year";
               // } else {
                   // $title= "$year $month {$weekOfYear}th Week Attendance of FORTIGRID";
                //}
                $LoadDateTimepicker=true;
                $employees = User::with('roles')->whereHas('attendances', function ($q) use ($dateRange) {
                    $q->whereBetween('chkinDate', [$dateRange[0], end($dateRange)]);
                })  
                 ->whereHas('roles', function ($q) {
                                            $q->whereNotIn('roles.id', config('global.role_without_attendance'))
                                            ->where('roles.id', '!=', session('role_id'));
                                        });
                    if(in_array(session('role_id'),config('global.task_monitor_roles'))){
                        $employees->whereHas('roles', function ($q) {
                                $q->where('roles.id','!=',config('global.first_level_role'));
                                 $q->where('roles.id','!=',config('global.task_monitor_roles'));
                        })  ;
                    }
                    $employees = $employees->get(); 
                // $download=    Excel::download(
                //         new AttendanceExport($startDate, $endDate, $teamTypeId),
                //         'attendance_report.xlsx'
                //         );
                //dd( $employees);
                return view('reports.attendance_report', compact('employees', 'dateRange','LoadDateTimepicker','title'));
    }
    public function generate_report(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        return view('reports.generate_report');
    }
    public function ticket_reports(){
            if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
                return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
            }
                        
            $support_team  = TeamMembers::whereHas('team', function ($query) {
                                $query->where('team_type', 3);
                            })
                            ->with('user:name,id') // eager load only name and id
                            ->get()
                            ->pluck('user.name','user.id')
                            ->filter()
                            ->unique();
            $project_status= ProjectStatus::where('ticket_set_status',config('global.task_set_status'))->pluck('proj_status_name','id');
            $LoadDateTimepicker=true;
            $LoadDatatables = true;
            return view('reports.tickets_report', compact('support_team', 'LoadDateTimepicker','project_status','LoadDatatables'));  
    }
   
public function ticket_report_action(Request $request)
{
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');    
    $teamMemId = $request->input('members_id');
    $ticketStatus = $request->input('ticket_status');

    // Convert to Y-m-d format
    if ($startDate && $endDate) {
        $start = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
        $end = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
    } else {
        $start = Carbon::now()->subDays(7)->startOfDay();
        $end = Carbon::now()->endOfDay();
    }


    // Fetch all tickets, even those without assigned members
    $main_query = RaiseTicket::with([
        'AssignedTicketMembers',
        'ticketType',
        'problemType',
        'ticketStatus',
        'TicketOwner' // Include this for ticket_owner column
    ]);

    if ($ticketStatus) {
        $main_query->where('ticket_status', $ticketStatus);
    }

    if ($startDate && $endDate) {
        $main_query->whereBetween('created_at', [$start, $end]);
    }
if ($teamMemId) {
    $ids = is_array($teamMemId) ? $teamMemId : explode(',', $teamMemId);
    $main_query->whereHas('AssignedTicketMembers', function ($query) use ($ids) {
        $query->whereIn('assign_mem_id', $ids);
    });
}


    $tickets = $main_query->get();

    // Annotate tickets with assignment status if member ID is provided


    return DataTables::of($tickets)
        ->addIndexColumn()
        ->addColumn('ticket_type', fn($row) => $row->ticketType->ticket_type ?? 'N/A')
        ->addColumn('problem_type', fn($row) => $row->problemType?->problem_type ?? 'N/A')
        ->addColumn('ticket_name', fn($row) => $row->ticket_name)
        ->addColumn('ticket_owner', fn($row) => $row->TicketOwner->name ?? 'N/A')
        ->addColumn('created_date', fn($row) => Carbon::parse($row->created_at)->format('d/m/Y h:i A'))
        ->addColumn('ticket_status', function ($row) {
            $badge = config('global.task_status_badges')[$row->ticketStatus?->id] ?? ['label' => 'Unknown', 'class' => 'badge-secondary'];
            return '<label class="badge ' . $badge['class'] . '">' . $badge['label'] . '</label>';
        })
        ->addColumn('assigned_members', function ($row) {
            if ($row->AssignedTicketMembers->isEmpty()) {
                return '<ul><li>No members assigned</li></ul>';
            }

            $list = '<ul>';
            foreach ($row->AssignedTicketMembers as $member) {
                $list .= '<li>' . ($member->user->name ?? 'Unknown') . '</li>';
            }
            $list .= '</ul>';

            return $list;
        })
        ->rawColumns(['ticket_status','assigned_members'])
        ->make(true);
}

public function exportTicketReport(Request $request)
{
    $filters = $request->only(['start_date', 'end_date', 'ticket_status', 'members_id']);
    return Excel::download(new TicketReportExport($filters), 'ticket_report.xlsx');
}
public function inventory_report(){
     if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
                return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }             
            $itemTypes=ItemType::where('item_status',config('global.active_status'))->pluck('item_type_name','id');
            $BrandTypes=Brands::where('brand_status',config('global.active_status'))->pluck('brand_name','id');
            $user_info=User::where('emp_status',config('global.active_status'))->where('support_access','!=',config('global.active_status'))->pluck('name','id');
            $LoadDateTimepicker=true;
            $LoadDatatables = true;
            return view('reports.inventory_report', compact('itemTypes', 'LoadDateTimepicker','BrandTypes','LoadDatatables','user_info'));  
}
public function inventory_report_action(Request $request)
{
    $inventory_type = $request->input('inventory_type');
    $brand = $request->input('brand');    
    $user_id = $request->input('user_id');

    // Convert to Y-m-d format
 $main_query = Inventory::with('assignments');

    if ($inventory_type) {
        $main_query->where('asset_type', $inventory_type);
    }
    if ($brand) {
        $main_query->where('asset_brand', $brand);
    }
    if ($user_id) {
        $main_query->whereHas('assignments.employee', function ($subQuery) use ($user_id) {
                $subQuery->where('id', $user_id);
            });
    }


    $tickets = $main_query->get();

    // Annotate tickets with assignment status if member ID is provided


    return DataTables::of($tickets)
        ->addIndexColumn()
        ->addColumn('inventory_type', fn($row) => $row->AssetType->item_type_name ?? 'N/A')
        ->addColumn('brand', fn($row) => $row->AssetBrand->brand_name ?? 'N/A')
        ->addColumn('inventory_name', fn($row) => $row->asset_name)
        //->addColumn('assigned_employee', fn($row) => $row->assignments?? 'N/A')
        ->addColumn('assigned_employee', function($row) {
                if ($row->assignments->isNotEmpty()) {
                    // Assuming each assignment has an `employee` relation with `name`
                    return $row->assignments->map(fn($a) => $a->employee->name)->implode(', ');
                }
                return '-';
            })

        ->make(true);
}
public function exportInventoryReport(Request $request){
     $filters = $request->only(['inventory_type', 'brand', 'user_id']);
    return Excel::download(new InventoryReportExport($filters), 'inventory_report.xlsx');
}
public function asset_report(){
  if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
                return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }             
            $itemTypes=AssetTypes::where('asset_type_status',config('global.active_status'))->pluck('asset_type_name','id');
            $BrandTypes=Brands::where('brand_status',config('global.active_status'))->pluck('brand_name','id');
            $user_info=User::where('emp_status',config('global.active_status'))->where('support_access','!=',config('global.active_status'))->pluck('name','id');
            $LoadDateTimepicker=true;
            $LoadDatatables = true;
            return view('reports.asset_report', compact('itemTypes', 'LoadDateTimepicker','BrandTypes','LoadDatatables','user_info'));  
}
public function asset_report_action(Request $request){
    $asset_type = $request->input('item_type');
    $asset_category = $request->input('item_category');
    $brand = $request->input('brand');    
    $user_id = $request->input('user_id');

    // Convert to Y-m-d format
 $main_query = AssetItems::with('assignments');

    if ($asset_type) {
        $main_query->where('item_type', $asset_type);
    }
    if($asset_category){
        $main_query->where('item_category', $asset_category);
    }
    if ($brand) {
        $main_query->where('item_brand', $brand);
    }
    if ($user_id) {
        $main_query->whereHas('assignments.employee', function ($subQuery) use ($user_id) {
                $subQuery->where('id', $user_id);
            });
    }


    $tickets = $main_query->get();

       return DataTables::of($tickets)
        ->addIndexColumn()
        ->addColumn('item_type', fn($row) => $row->item_type)
         ->addColumn('item_category', function($row) {
                                return $row->item_category_name; // uses the accessor defined above
                            })
         ->addColumn('item_brand', function($row) {      
                                return $row->itemBrand->brand_name;
                            })
        ->addColumn('inventory_name', fn($row) => $row->asset_name)
        //->addColumn('assigned_employee', fn($row) => $row->assignments?? 'N/A')
        ->addColumn('assigned_employee', function($row) {
                if ($row->assignments->isNotEmpty()) {
                    // Assuming each assignment has an `employee` relation with `name`
                    return $row->assignments->map(fn($a) => $a->employee->name)->implode(', ');
                }
                return '-';
            })

        ->make(true);
}
public function exportAssetReport(Request $request){
     $filters = $request->only(['item_type','item_category', 'brand', 'user_id']);
    return Excel::download(new AssertReportExport($filters), 'assert_report.xlsx');
}
    public function asset_report_hrms(Request $request){
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $itemTypes=AssetTypes::where('asset_type_status',config('global.active_status'))->pluck('asset_type_name','id');
        $BrandTypes=Brands::where('brand_status',config('global.active_status'))->pluck('brand_name','id');
        $user_info=User::where('emp_status',config('global.active_status'))->where('support_access','!=',config('global.active_status'))->pluck('name','id');
        $LoadDatatables=true;
        $LoadDateTimepicker = true;
        return view('reports.asset_report_hrms', compact('BrandTypes','itemTypes','user_info','LoadDatatables','LoadDateTimepicker'));
    }
    public function asset_report_action_hrms(Request $request){
            $asset_type = $request->input('item_type');
            $asset_category = $request->input('item_category');
            $brand = $request->input('brand');
            $user_id = $request->input('user_id');
            $attrIds = $request->input('search_configure_attribute', []); 

            // Convert to Y-m-d format
             $main_query = AssetItems::with('assignments','ItemConfigurationValues');

            if ($asset_type) {
                $main_query->where('item_type', $asset_type);
            }
            if($asset_category){
                $main_query->where('item_category', $asset_category);
            }
            if ($brand) {
                $main_query->where('item_brand', $brand);
            }
            if ($user_id) {
                $main_query->whereHas('assignments.employee', function ($subQuery) use ($user_id) {
                        $subQuery->whereIn('id', $user_id);
                });
            }
            if($attrIds){
                $main_query->whereHas('ItemConfigurationValues', function ($subQuery) use ($attrIds) {
                        $subQuery->whereIn('option_id', $attrIds);
                    });
            }


            $tickets = $main_query->get();
            return DataTables::of($tickets)
                ->addIndexColumn()
                ->addColumn('item_type', fn($row) => $row->item_type)
                ->addColumn('item_category', function($row) {
                                        return $row->item_category_name; // uses the accessor defined above
                                    })
                ->addColumn('item_brand', function($row) {      
                                        return $row->itemBrand->brand_name;
                                    })
                ->addColumn('inventory_name', fn($row) => $row->asset_name)
                //->addColumn('assigned_employee', fn($row) => $row->assignments?? 'N/A')
                ->addColumn('assigned_employee', function($row) {
                        if ($row->assignments->isNotEmpty()) {
                            // Assuming each assignment has an `employee` relation with `name`
                            return $row->assignments->map(fn($a) => $a->employee->name)->implode(', ');
                        }
                        return '-';
                    })
                ->make(true);

    }
    public function exportAssetReportHRMS(Request $request){
      // Grab the filters you already have
    $filters = $request->only(['item_type','item_category', 'brand', 'user_id']);

    // Grab the checkbox array (defaults to empty if nothing selected)
    $attributes = $request->input('search_configure_attribute', []);

    // Merge them together
    $filters['search_configure_attribute'] = $attributes;

    return Excel::download(new AssertReportExportHRMS($filters), 'assert_report_hrms.xlsx');
}
}
