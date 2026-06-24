<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Carbon;
use App\Helpers\ActivityHelper;

class HolidaysController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        if ($request->ajax()) {
        $holiday_model = Holiday::get();
        return DataTables::of($holiday_model)
            ->addIndexColumn()
            ->addColumn('holiday_dtls', function ($row) {
                    $fromDate = $row->from_dt 
                        ? Carbon::parse($row->from_dt)->format('d-m-Y') 
                        : '';

                    $toDate = $row->to_dt 
                        ? Carbon::parse($row->to_dt)->format('d-m-Y') 
                        : '';

                    return $toDate 
                        ? "From {$fromDate} to {$toDate}" 
                        : $fromDate;
                })
            ->addColumn('action', function($row) {
                return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
                    <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i></button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('holidays.holidays_list',['LoadDatatables' => true,'LoadDateTimepicker' => true]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
              'holiday_name' => 'required|unique:office_holidays',
              'from_dt' => 'required',
          ]);
         $data = $request->except('_token');
         $data['from_dt']=Carbon::parse($request->from_dt)->format('Y-m-d');
         $data['to_dt']=!empty($request->to_dt) ? Carbon::parse($request->to_dt)->format('Y-m-d') : null;
          // Mass assigment
          $newRecord= Holiday::create($data);
          $id= $newRecord->id;
          $log_type="Holiday";
         $this->logProcessActivity('Holiday created', $newRecord,$log_type);
        return  response()->json(['message' => 'Holiday details Added successfully!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $Holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        // Format dates safely
        $holiday->from_dt = $holiday->from_dt 
            ? Carbon::parse($holiday->from_dt)->format('d-m-Y') 
            : null;

        $holiday->to_dt = $holiday->to_dt 
            ? Carbon::parse($holiday->to_dt)->format('d-m-Y') 
            : null;

        // Return JSON only if permission is granted
        if (PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
            return response()->json($holiday);
        }

        return redirect()
            ->route('dashboard')
            ->withMessage('Not Authorized to see this page.');
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'holiday_name' => 'required|unique:office_holidays,holiday_name,' . $holiday->id . ',id',
            'from_dt' => 'required',
        ]);

        $data = $request->except(['_token', '_method']);

        $data['from_dt'] = Carbon::parse($request->from_dt)->format('Y-m-d');
        $data['to_dt']   = $request->filled('to_dt')
            ? Carbon::parse($request->to_dt)->format('Y-m-d')
            : null;

        $holiday->update($data);
         $log_type="Holiday";
         $this->logProcessActivity('Holiday Updated', $holiday,$log_type);
        return response()->json([
            'message' => 'Holiday details updated successfully!',
            'holiday' => $holiday
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $Holiday){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
        if(!$cat_permission){ 
            return  response()->json(['message' => 'Not Authorized to see this page.'],200);
        }else{
            if ($Holiday) {
            $log_name='Holiday';
            $this->logProcessActivity('Holiday Deleted', $Holiday,$log_name);
                $Holiday->delete();
            }
            return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }
}
