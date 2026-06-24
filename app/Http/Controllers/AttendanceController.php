<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Exports\AttendanceReportExport;
use App\Helpers\ActivityHelper;
use App\Helpers\PermissionHelper;
use App\Imports\AttendanceImport;
//use App\Jobs\ImportPunchCardJob;
use App\Models\Attendance;
use App\Models\Leaveinfo;
use App\Models\PermitModule;
use App\Models\PunchAttendance;
use App\Models\Timesheet;
use App\Models\User;
use App\Models\WorkingMode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
//use Illuminate\Support\Facades\Storage;
class AttendanceController extends Controller
{
    public function mark_attendance()
    {
        if (! PermissionHelper::checkPermission('global.categories', $this->add_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }

        return view('my_profile.mark_attendance');
    }

    public function index(Request $request)
    {

        if (! PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        if ($request->ajax()) {
            $work_mode = WorkingMode::get();

            return DataTables::of($work_mode)
                ->addIndexColumn()
                ->addColumn('mode_status', function ($row) {
                    if ($row->mode_status == 1) {
                        $status = 'Active';
                    } else {
                        $status = 'InActive';
                    }

                    return $status;
                })
                ->addColumn('action', function ($row) {
                    return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
                    <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i></button>
                ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('attendance.working_mode', ['LoadDatatables' => true]);

    }

    public function getEnabledSlotCount($checkIn_time, $user_id)
    {
        $checkIn = Carbon::parse($checkIn_time);
        $today = Carbon::today();

        $permissionSlots = Leaveinfo::where('emp_id', $user_id)
            ->whereDate('from_dt', $today)
            ->where('leave_type', 2)
            ->where('leave_status', config('global.leave_approved'))
            ->select('from_time', 'to_time')
            ->get();
        // logger()->info('permissionSlots set');
        // logger()->info('permissionSlots', ['permissionSlots'=>$permissionSlots]);
        $times = [
            '9:00am - 10:00am', '10:00am - 10:45am', '11:15am - 12:00pm',
            '12:00pm - 1:00pm', '1:30pm - 2:00pm', '2:00pm - 3:00pm',
            '3:00pm - 4:00pm', '4:15pm - 5:00pm', '5:00pm - 6:00pm',
        ];

        $enabledCount = 0;

        foreach ($times as $slot) {
            [$from, $to] = array_map('trim', explode('-', $slot));
            $fromTime = Carbon::parse($from);
            $toTime = Carbon::parse($to);

            if ($checkIn->between($fromTime, $toTime, false)) {
                $fromTime = $checkIn;
            }

            $isBlockedByPermission = $permissionSlots->filter(function ($perm) use ($fromTime, $toTime) {
                $permFrom = Carbon::parse($perm->from_time);
                $permTo = Carbon::parse($perm->to_time);

                return $fromTime < $permTo && $toTime > $permFrom;
            })->isNotEmpty();
            // $fromTime->greaterThanOrEqualTo($checkIn)
            if (! $isBlockedByPermission && $fromTime->greaterThanOrEqualTo($checkIn)) {
                $enabledCount++;
            }
        }

        return $enabledCount;
    }

    public function attendance_update_mode(Request $request)
    {
        try {
            $inputTime = $request->input('chkinDate');
            // dd($request->input('sys_prob'));
            $checkIn = Carbon::createFromFormat('Y-m-d h:i A', now()->format('Y-m-d').' '.$inputTime);
            $entry_chkin_time = now()->format('Y-m-d h:i A');
            if ($request->input('sys_prob') == 1) {
                $slots = $this->getEnabledSlotCount($entry_chkin_time, session('user_id'));
            } else {
                $slots = $this->getEnabledSlotCount($checkIn, session('user_id'));
            }
            $work_mode = $request->input('working_mode');
            $comments = $request->input('comments');
            $login_chk = Attendance::where('emp_id', session('user_id'))
                ->whereDate('chkinDate', Carbon::today())
                ->first();
            if (! ($login_chk)) {
                $attendance = Attendance::create([
                    'emp_id' => session('user_id'),
                    'chkinDate' => $checkIn,
                    'working_mode' => $request->input('working_mode'),
                    'comments' => $request->input('comments'),
                    'timesheet_slot' => $slots,
                    'sys_problem' => $request->input('sys_prob'),
                ]);
            } else {
                $login_chk->working_mode = $work_mode;
                $login_chk->comments = $comments;
                $login_chk->save();
            }
            session(['checked_attendance' => true]);
            $chkin_time = date('H:i:s', strtotime($checkIn));
            session(['chkin_time' => $chkin_time,
                'timeslots' => $slots,
                'sys_problem' => $request->input('sys_prob'),
                'last_prob_time' => now()->format('H:i:s'),
                'reason_chkin' => $comments,
            ]);
            $inputCarbon = Carbon::parse($inputTime);
            $comparisonTime = Carbon::today()->setTime(9, 15);

            if ($inputCarbon->gt($comparisonTime)) {
                $notify_type = 'attendance';
                $subject = 'Fortgrid - Late Checkin by '.session('user_name');
                $message = 'Following user have logged in late. The details are as follows:<br/>';
                $message .= '<br/>Name:'.session('user_name');
                $message .= '<br/>Login Time:'.$inputTime;
                $message .= '<br/>Reason:'.$request->input('comments').'<br/>';
                $message .= '<br/>Checkin Date:'.date('jS F, Y', strtotime($checkIn)).'<br/>';
                $this->AttendanceNotification($notify_type, $subject, $message);

            }
            if ($request->input('sys_prob') == 1) {
                $add_entries = $this->storeSystemProblemEntries();
            }
            Session::forget('sys_problem');

            return response()->json(['message' => 'Successfully Checked In for the Day.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function attendance_update_irregular_chkout(Request $request)
    {
        try {
            /**Checkout Process */

            // Parse last check-in date
            $inputDate = Carbon::parse($request->input('last_checkin_dt'));

            // Build checkout datetime (combine date + checkout time)
            $checkout = Carbon::createFromFormat(
                'Y-m-d h:i A',
                $inputDate->format('Y-m-d').' '.$request->input('chkoutTime')
            );
            // Build previous check-in datetime (combine date + check-in time)
            $prev_day_time = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $inputDate->format('Y-m-d').' '.$request->input('last_checkin_time')
            );

            // Calculate duration in minutes
            $minutesWorked = $prev_day_time->diffInMinutes($checkout);

            // Prevent negative durations
            if ($minutesWorked < 0) {
                $minutesWorked = 0;
            }

            // Convert minutes to HH:MM format
            $hours = intdiv($minutesWorked, 60);
            $minutes = $minutesWorked % 60;
            $formattedDuration = sprintf('%02d:%02d', $hours, $minutes);

            // Check if user has already logged in today
            $login_chk = Attendance::where('emp_id', session('user_id'))
                ->whereDate('chkinDate', Carbon::today())
                ->first();

            if (! $login_chk) {
                Attendance::where('emp_id', session('user_id'))
                    ->whereDate('chkinDate', $inputDate->format('Y-m-d'))
                    ->update([
                        'chkoutDate' => $checkout,
                        'work_duration' => $minutesWorked,       // raw minutes
                        'late_checkout_reason' => $request->input('chkout_reason'),
                    ]);
            }
            $notify_type = 'attendance';
            $subject = 'Fortgrid - Early Checkout by '.session('user_name');
            $message = 'Following user has not Checked out on '.session('last_checkin_dt').' The details are as follows:<br/>';
            $message .= '<br/>Name:'.session('user_name');
            $message .= '<br/>Checkout Time:'.$request->input('chkoutTime');
            $message .= '<br/>Reason:'.$request->input('chkout_reason').'<br/>';

            $this->AttendanceNotification($notify_type, $subject, $message);

            /**Checkout Process */
            /**Timesheet Process */
            if ((int) session('incomplete_timesheet') === 1) {
                $date = Carbon::createFromFormat('d-m-Y', session('last_checkin_dt'))->format('Y-m-d');
                $query_timeslot = Timesheet::where('emp_id', session('user_id'))
                    ->whereDate('create_dt', $date);
                // Check if any record exists
                if ($query_timeslot->exists()) {
                    // At least one timesheet exists for this employee on that date
                    $firstSlot = $query_timeslot->first();  // get the first slot if needed
                    $filledSlots = $query_timeslot->count();
                } else {
                    // No timesheet exists for this employee on that date
                    $firstSlot = null;
                    $filledSlots = 0;
                }

                if ((int) $request->worked_period === 2) {
                    if ($firstSlot) {
                        return response()->json(['url' => 'edit_timesheet/'.$firstSlot->id]);
                    } else {
                        session(['incomplete_timesheet' => 1]);

                        return response()->json(['url' => 'fill_timesheet/']);
                    }
                } else {
                    if ($filledSlots >= 4) {
                        session(['incomplete_timesheet' => 0]);

                        return response()->json(['url' => '/dashboard']);
                    } else {
                        session(['incomplete_timesheet' => 1]);

                        return response()->json(['url' => 'fill_timesheet/']);
                    }

                }
                /**Timesheet Process */
            } else {
                session()->forget(['last_checkin_dt', 'last_checkin_time']);
                session(['irregular_chkout' => 0]);

                return response()->json([
                    'message' => 'Successfully Checked Out for the Day.',
                    'duration' => $minutesWorked,        // raw minutes
                    'duration_hm' => $formattedDuration,    // "07:41"
                    'chkin' => $prev_day_time->toDateTimeString(),
                    'chkoutDate' => $checkout->toDateTimeString(),
                    'url' => '/dashboard',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'work_mode_name' => 'required|unique:working_mode',
            'mode_status' => 'required',
        ]);
        $data = $request->all();
        $data['work_mode_name'] = $request->input('work_mode_name');
        $data['mode_status'] = $request->input('mode_status');

        // Mass assigment
        $work_mode = WorkingMode::create($data);
        $log_name = 'working_mode';
        ActivityHelper::logActivity('Working Mode Type created', $log_name, $work_mode, [
            'request' => request()->all(),
        ]);

        return response()->json(['success' => 'Working Mode Type details Added successfully!']);
    }

    public function edit($id)
    {
        $notify_typs = WorkingMode::find($id);

        return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($notify_typs) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');

    }

    public function update(Request $request)
    {
        $work_mode = WorkingMode::find($request->id);
        $log_name = 'working_mode';
        ActivityHelper::logActivity('Working Mode Type Edited', $log_name, $work_mode, [
            'request' => request()->all(),
        ]);
        $data = $request->validate([
            'work_mode_name' => 'required|unique:working_mode,work_mode_name,'.$request->id,
            'mode_status' => 'required',
        ]);
        $data = $request->all();
        $data['work_mode_name'] = $request->input('work_mode_name');
        $data['mode_status'] = $request->input('mode_status');
        $work_mode->update($data);

        return response()->json(['success' => 'Working Mode Type details updated successfully!', 'work_mode' => $work_mode]);
    }

    public function destroy($id)
    {
        $cat_permission = PermissionHelper::checkPermission('global.categories', $this->del_perm);
        if (! $cat_permission) {
            return response()->json(['message' => 'Not Authorized to see this page.'], 200);
        } else {
            $work_mode = WorkingMode::find($id);
            if ($work_mode) {
                $log_name = 'working_mode';
                ActivityHelper::logActivity('Working Mode Type Deleted', $log_name, $work_mode, [
                    'request' => request()->all(),
                ]);
                $work_mode->delete();
            }

            return response()->json(['message' => 'Record Deleted successfully!'], 200);
        }
    }

    public function attendance_info()
    {
        $date = date('Y-m-d');
        $loadSelect2JS = true;
        $savedSlots = Timesheet::where('emp_id', session('user_id'))
            ->where('create_dt', $date)
            ->get();
        $permissionSlots = Leaveinfo::where('emp_id', session('user_id'))
            ->whereDate('from_dt', Carbon::today())
            ->where('leave_status', 1)
            ->select('from_time', 'to_time')
            ->get()
            ->map(function ($slot) {
                return [
                    'from_time' => $slot->from_time,
                    'to_time' => $slot->to_time,
                ];
            });
        $team_ids = session('team_id');
        $emp_query = User::with('roles')->where('emp_status', config('global.active_status'))
            ->where('id', '!=', config('global.superadmin_id'))
            ->where('id', '!=', session('user_id'))
            ->whereHas('roles', function ($query) {
                $query->whereNotIn('roles.id', config('global.monitor_employees_act'));
            });
        $employees = $emp_query->get();

        return view('attendance.attendance_info', compact('employees', 'loadSelect2JS', 'savedSlots', 'permissionSlots'));
    }

    public function getCalendarEvents(Request $request)
    {
        $empId = $request->input('emp_id');
        $start = $request->input('start');
        $end = $request->input('end');

        $attendances = Attendance::where('emp_id', $empId)
            ->whereBetween('chkinDate', [$start, $end])
            ->get();

        $events = [];

        foreach ($attendances as $record) {
            $checkIn = Carbon::parse($record->chkinDate);
            $checkOut = $record->chkoutDate ? Carbon::parse($record->chkoutDate) : null;
            $dateStr = Carbon::parse($record->chkinDate)->format('Y-m-d');
            $chkexist = Timesheet::where('emp_id', $empId)
                ->where('create_dt', $dateStr)
                ->exists();
            $eventStart = $checkIn->toDateTimeString();
            $eventEnd = null;

            // If checkout is missing AND the date is in the past (not today)
            if (! $checkOut && $checkIn->isBefore(Carbon::today())) {
                $eventEnd = $checkIn->copy()->setTime(18, 0)->toDateTimeString(); // 6:00 PM
            } elseif ($checkOut) {
                $eventEnd = $checkOut->toDateTimeString();
            }
            $isToday = $dateStr === Carbon::today()->format('Y-m-d');
            $events[] = [
                'title' => 'Attendance',
                'start' => $eventStart,
                'end' => $eventEnd,
                'color' => '#6f42c1',
                'extendedProps' => [
                    'checkin' => $checkIn->format('g:i A'),
                    'checkout' => $checkOut ? $checkOut->format('g:i A') : ($eventEnd ? '6:00 PM (System Default time)' : '-'),
                    'has_tasks' => $chkexist,
                    'is_today' => $isToday,
                ],
            ];
        }

        return response()->json($events);
    }

    public function applied_leaves(Request $request)
    {
        if (! PermissionHelper::checkPermission('global.categories', $this->add_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $LoadDateTimepicker = true;
        $LoadDatatables = true;
        if ($request->ajax()) {
            $leaves = Leaveinfo::where('emp_id', session('user_id'));
            if ($request->leave_type && $request->leave_type != 0) {
                $leaves->where('leave_type', $request->leave_type);
            }
            if ($request->from_dt && $request->to_dt) {
                $fromDate = Carbon::parse($request->from_dt)->format('Y-m-d');
                $toDate = Carbon::parse($request->to_dt)->format('Y-m-d');
                if ($request->leave_type == 1) {
                    $leaves->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereDate('from_dt', '<=', $toDate)
                            ->whereDate('to_dt', '>=', $fromDate);
                    });
                } else {
                    $leaves->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereBetween('from_dt', [$fromDate, $toDate]);
                    });
                }
            }
            $leaves = $leaves->orderBy('id', 'desc');

            // This will print the full SQL with values
            // $sql = vsprintf(
            //     str_replace('?', "'%s'", $leaves->toSql()),
            //     $leaves->getBindings()
            // );
            // echo $sql;
            return DataTables::of($leaves)
                ->addIndexColumn()
                ->addColumn('leave_type', function ($row) {
                    return ($row->leave_type == 1) ? 'Leave' : 'Permission';
                })
                ->addColumn('applied_date', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y');
                })
                ->addColumn('details', function ($row) {
                    $fromDate = Carbon::parse($row->from_dt)->format('d-m-Y');

                    if ($row->leave_type == 1) {
                        $toDate = Carbon::parse($row->to_dt)->format('d-m-Y');

                        return 'From  '.$fromDate.' to '.$toDate;
                    } else {
                        try {
                            $fromTime = Carbon::createFromFormat('H:i:s', $row->from_time)->format('g:i A');
                            $toTime = Carbon::createFromFormat('H:i:s', $row->to_time)->format('g:i A');
                        } catch (\Exception $e) {
                            $fromTime = $row->from_time;
                            $toTime = $row->to_time;
                        }

                        return 'Date: '.$fromDate.'<br/>Timings From '.$fromTime.' to '.$toTime;
                    }
                })
                ->addColumn('leave_status', function ($row) {
                    switch ($row->leave_status) {
                        case 0:
                            return '<span class="badge blinking">Waiting for Approval</span>';
                        case 1:
                            return '<span class="badge badge-success" data-bs-toggle="tooltip" data-bs-html="true" title="'.htmlspecialchars_decode('Comments: '.e($row->reason_status)).'">Approved</span>';
                        case 2:
                            return '<span class="badge badge-danger"  data-bs-html="true" data-bs-toggle="tooltip" title="'.htmlspecialchars_decode('Comments: '.e($row->reason_status).'">').'Rejected</span>';
                        default:
                            return '<span class="badge badge-secondary">Unknown</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-eye" title="active"></i></button>';
                })
                ->rawColumns(['action', 'leave_status', 'details'])
                ->make(true);
        }

        return view('my_profile.applied_leaves', compact('LoadDateTimepicker', 'LoadDatatables'));
    }

    public function change_leave_state(Request $request)
    {
        //  if (!PermissionHelper::checkPermission('global.categories', $this->edit_perm)) {
        //         return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        //     }
        $leave_info = Leaveinfo::find($request->recordId);
        $data['leave_status'] = $request->input('leave_status');
        $data['approved_by'] = session('user_id');
        $data['reason_status'] = $request->input('comments');
        $leave_info->update($data);
        $log_name = 'leave/permission';
        ActivityHelper::logActivity('Leave Status Updated', $log_name, $leave_info, [
            'request' => request()->all(),
        ]);
        $today = Carbon::today();
        $permissionDate = Carbon::parse($request->permission_dt1)->format('Y-m-d');
        $leaveType = $request->input('leave_type1');
        $leave_status = $data['leave_status'] == 1 ? 'approved' : 'cancelled';
        //
        /**IF EMPLOYEE APPLIES PERMISSION FOR CURRENT DATE SO TIMESLOT SHOULD ADJUST THEN ONLY HE CAN CHECKOUT. */
        logger()->info('inputs', ['leaveType' => $request->input('leave_type1'), 'leave_status' => $data['leave_status'], '$permissionDate' => $permissionDate, 'today' => $today]);
        if ($leaveType == 2 && $data['leave_status'] == 1 && $permissionDate == $today->toDateString()) {
            $attendance_info = Attendance::where('emp_id', $request->input('emp_id'))
                ->whereDate('chkinDate', $today)
                ->select('chkinDate')
                ->first();
            if ($attendance_info) {
                $chkinTime = Carbon::parse($attendance_info->chkinDate);
                $slots = $this->getEnabledSlotCount($chkinTime, $request->input('emp_id'));
                $result = Attendance::where('emp_id', $request->input('emp_id'))
                    ->whereDate('chkinDate', $permissionDate)
                    ->update(['timesheet_slot' => $slots]);
            }
        }
        $notify_type = $leaveType == 1 ? 'leave_approval' : 'permission_approval';

        $subject = 'Fortgrid - '.$request->input('emp_name')."'s ".ucfirst(str_replace('_', ' ', $notify_type)).' Request status Changed.';

        if ($request->input('leave_type1') == 1) {
            $message = $request->input('emp_name')." 's  Leave Request has been ".ucfirst($leave_status).'.The Details are as follows:<br/><br/>';
        } else {
            $message = $request->input('emp_name')." 's  Permission Request has been ".ucfirst($leave_status).'.The Details are as follows:<br/><br/>';
        }

        if ($leaveType == 1) {
            if ($request->input('from_dt1') == $request->input('to_dt1')) {
                $message .= '<br/>Date:'.$request->input('from_dt1').'<br/>';
            } else {
                $message .= '<br/>From Date:'.$request->input('from_dt1').'<br/>';
                $message .= '<br/>To Date:'.$request->input('to_dt1').'<br/>';
            }
        } else {
            $message .= '<br/>Permission Date:'.$request->input('permission_dt1').'<br/>';
            $message .= '<br/>Timings:'.$request->input('from_time1').'-'.$request->input('to_time1').'<br/>';
        }
        $message .= '<br/>Leave Status: '.ucfirst($leave_status);
        // $message .= "<br/>Reason for ".$leave_status.':' . $data['reason_status']. '<br/>';

        $this->AttendanceNotification($notify_type, $subject, $message, $request->input('emp_id'), $selfsent = true, $data['leave_status']);

        return response()->json(['message' => 'Leave Status updated successfully!']);
    }

    public function leave_approval_requests(Request $request)
    {
        if (! PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }

        $LoadDateTimepicker = true;
        $LoadDatatables = true;
        $control_teams = '';
        $empIds = '';
        $result = '';
        $module = 'leave_module';

        $currDate = Carbon::today()->format('Y-m-d');
        $pmId = session('user_id');

        if (in_array(session('role_id'), config('global.all_in_all_access'))) {
            $control_teams = User::with('roles')
                ->whereNotIn('id', [$pmId])
                ->whereHas('roles', function ($query) {
                    $query->whereIn('roles.id', config('global.task_monitor_roles'));
                })
                ->get();
            $result = true;
        } elseif (in_array(session('role_id'), config('global.task_approve_roles'))) {
            $result = PermitModule::where('emp_id', session('user_id'))
                ->where('module_name', $module)
                ->exists();
        } else {
            $control_teams = collect();
            $empIds = collect();
            $result = '';
        }

        if ($request->ajax()) {
            $leaves = Leaveinfo::join('roles_user', 'leave_info.emp_id', '=', 'roles_user.user_id')
                ->join('users', 'leave_info.emp_id', '=', 'users.id')
                ->whereDate('leave_info.from_dt', '>=', $currDate)
                ->select([
                    'leave_info.id as leave_id',
                    'leave_info.emp_id',
                    'leave_info.leave_type',
                    'leave_info.reason',
                    'leave_info.from_dt',
                    'leave_info.to_dt',
                    'leave_info.from_time',
                    'leave_info.to_time',
                    'leave_info.leave_status',
                    'leave_info.reason_status',
                    'leave_info.created_at',
                    'roles_user.roles_id',
                    'users.name as emp_name',
                    'users.email',
                ]);

            if (in_array(session('role_id'), config('global.task_approve_roles'))) {
                $leaves->whereNotIn('roles_user.roles_id', config('global.monitor_employees_act'));
            } else {
                $leaves->whereNotIn('roles_user.roles_id', config('global.first_level_role'));
            }

            if ($request->leave_type && $request->leave_type != 0) {
                $leaves->where('leave_info.leave_type', $request->leave_type);
            }

            if ($request->from_dt && $request->to_dt) {
                $fromDate = Carbon::parse($request->from_dt)->format('Y-m-d');
                $toDate = Carbon::parse($request->to_dt)->format('Y-m-d');

                if ($request->leave_type == 1) {
                    $leaves->where(function ($query) use ($fromDate, $toDate) {
                        $query->whereDate('leave_info.from_dt', '<=', $toDate)
                            ->whereDate('leave_info.to_dt', '>=', $fromDate);
                    });
                } else {
                    $leaves->whereBetween('leave_info.from_dt', [$fromDate, $toDate]);
                }
            }

            $leaves = $leaves->orderBy('leave_info.id', 'desc')
                ->orderBy('leave_info.leave_status', 'asc');

            return DataTables::of($leaves)
                ->addIndexColumn()
                ->addColumn('leave_type', fn ($row) => $row->leave_type == 1 ? 'Leave' : 'Permission')
                ->addColumn('emp_name', fn ($row) => $row->emp_name)
                ->addColumn('applied_date', fn ($row) => Carbon::parse($row->created_at)->format('d-m-Y'))
                ->addColumn('leave_status', function ($row) {
                    switch ($row->leave_status) {
                        case 0:
                            return '<span class="badge blinking">Waiting for Approval</span>';
                        case 1:
                            return '<span class="badge badge-success" data-bs-toggle="tooltip" data-bs-html="true" title="'
                                   .htmlspecialchars_decode('Comments: '.e($row->reason_status)).'">Approved</span>';
                        case 2:
                            return '<span class="badge badge-danger" data-bs-toggle="tooltip" data-bs-html="true" title="'
                                   .htmlspecialchars_decode('Comments: '.e($row->reason_status)).'">Rejected</span>';
                        default:
                            return '<span class="badge badge-secondary">Unknown</span>';
                    }
                })
                ->addColumn('details', function ($row) {
                    $fromDate = Carbon::parse($row->from_dt)->format('d-m-Y');
                    if ($row->leave_type == 1) {
                        $toDate = Carbon::parse($row->to_dt)->format('d-m-Y');

                        return 'From '.$fromDate.' to '.$toDate;
                    } else {
                        try {
                            $fromTime = Carbon::createFromFormat('H:i:s', $row->from_time)->format('g:i A');
                            $toTime = Carbon::createFromFormat('H:i:s', $row->to_time)->format('g:i A');
                        } catch (\Exception $e) {
                            $fromTime = $row->from_time;
                            $toTime = $row->to_time;
                        }

                        return 'Date: '.$fromDate.'<br/>Timings From '.$fromTime.' to '.$toTime;
                    }
                })
                ->addColumn('action', fn ($row) => '<button data-id="'.$row->leave_id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-eye" title="active"></i></button>')
                ->rawColumns(['action', 'leave_status', 'details'])
                ->make(true);
        }

        return view('my_profile.leave_approval_requests', compact('LoadDateTimepicker', 'LoadDatatables', 'control_teams', 'result'));
    }

    public function request_off_day(Request $request)
    {
        $leaveType = $request->leave_type;
        $empId = session('user_id');

        $commonRules = [
            'leave_type' => 'required|string',
            'reason' => 'nullable|string',
        ];

        $fromDate = '';
        $toDate = null;
        $fromTime = null;
        $toTime = null;

        if ($leaveType == 1) {
            $rules = array_merge($commonRules, [
                'from_dt' => 'required|date',
                'to_dt' => 'required|date|after_or_equal:from_dt',
            ]);
            Validator::make($request->all(), $rules)->validate();

            $fromDate = Carbon::parse($request->from_dt)->format('Y-m-d');
            $toDate = Carbon::parse($request->to_dt)->format('Y-m-d');

        } elseif ($leaveType == 2) {
            $request->merge([
                'from_time' => strtoupper(trim($request->from_time)),
                'to_time' => strtoupper(trim($request->to_time)),
            ]);

            $rules = array_merge($commonRules, [
                'permission_dt' => 'required|date',
                'from_time' => 'required|date_format:h:i A',
                'to_time' => 'required|date_format:h:i A|after:from_time',
            ]);
            Validator::make($request->all(), $rules)->validate();

            $fromDate = Carbon::parse($request->permission_dt)->format('Y-m-d');
            $fromTime = Carbon::createFromFormat('h:i A', $request->from_time)->format('H:i:s');
            $toTime = Carbon::createFromFormat('h:i A', $request->to_time)->format('H:i:s');

        } else {
            return response()->json(['message' => 'Invalid leave type.'], 422);
        }

        $leave = Leaveinfo::firstOrCreate(
            [
                'emp_id' => $empId,
                'leave_type' => $leaveType,
                'from_dt' => $fromDate,
            ],
            [
                'to_dt' => $toDate,
                'from_time' => $fromTime,
                'to_time' => $toTime,
                'reason' => $request->reason,
            ]
        );

        if ($leave->wasRecentlyCreated) {
            $logLabel = $leaveType == 1 ? 'Leave Request' : 'Permission Request';
            ActivityHelper::logActivity($logLabel, 'leave/permission', $leave, [
                'request' => $request->all(),
            ]);

            $notify_type = $leaveType == 1 ? 'leave' : 'permission';
            $subject = 'Fortgrid - '.ucfirst($notify_type).' Request by '.session('user_name');
            $message = $this->buildNotificationMessage($request, $leaveType);
            $this->AttendanceNotification($notify_type, $subject, $message);

            return response()->json(['message' => 'Leave/Permission Request Added.'], 200);
        }

        return response()->json(['message' => 'Leave/Permission Request Already Exists.'], 409);
    }

    private function buildNotificationMessage(Request $request, $leaveType)
    {
        $message = session('user_name').' has requested for '.($leaveType == 1 ? 'leave' : 'permission').'. The details are as follows:<br/>';
        // $message .= "<br/>Employee Name: " . session('user_name');

        if ($leaveType == 1) {
            $message .= '<br/>From Date: '.$request->from_dt;
            $message .= '<br/>To Date: '.$request->to_dt;
        } else {
            $message .= '<br/>From Time: '.$request->from_time;
            $message .= '<br/>To Time: '.$request->to_time;
        }

        $message .= '<br/>Reason: '.$request->input('reason').'<br/>';
        $message .= "<br/><br/><a href='".route('attendance.leave_approval_requests')."' target='_blank'>Click the Link For more details.</a><br/>";

        return $message;
    }

    public function view_leave_info($id)
    {
        $leave_info = Leaveinfo::with('emp_name')->find($id);
        // $leave_info= Leaveinfo::where('id',$id)->first();
        if ($leave_info->from_dt) {
            $leave_info->from_dt = Carbon::parse($leave_info->from_dt)->format('d-m-Y');
        }
        if ($leave_info->to_dt) {
            $leave_info->to_dt = Carbon::parse($leave_info->to_dt)->format('d-m-Y');
        }
        if ($leave_info->to_time) {
            $leave_info->to_time = Carbon::createFromFormat('H:i:s', $leave_info->to_time)->format('g:i A');
        }
        if ($leave_info->from_time) {
            $leave_info->from_time = Carbon::createFromFormat('H:i:s', $leave_info->from_time)->format('g:i A');
        }

        return response()->json($leave_info);
    }

    public function assign_module(Request $request)
    {
        // $request->validate([
        //        'ctrl_id' => 'required|array',
        //         'ctrl_id.*' => 'integer|exists:users,id',
        //         'module_name' => 'required|string|max:255',
        //     ]);
        $userId = session('user_id');
        $moduleName = $request->module_name;
        $selectedEmpIds = $request->ctrl_id ?? [];

        // Fetch previously assigned emp_ids
        $previousEmpIds = PermitModule::where('assigned_by', $userId)
            ->where('module_name', $moduleName)
            ->pluck('emp_id')
            ->toArray();

        // Determine changes
        $unassignedEmpIds = array_diff($previousEmpIds, $selectedEmpIds);
        $newlyAssignedEmpIds = array_diff($selectedEmpIds, $previousEmpIds);

        // Delete unassigned records
        PermitModule::where('assigned_by', $userId)
            ->where('module_name', $moduleName)
            ->whereIn('emp_id', $unassignedEmpIds)
            ->delete();

        // Assign new records
        foreach ($selectedEmpIds as $empId) {
            PermitModule::firstOrCreate([
                'emp_id' => $empId,
                'assigned_by' => $userId,
                'module_name' => $moduleName,
            ]);
        }
        $subject = 'Fortgrid - Access For Leave Approval Request';
        $notify_type = 'permission_leave_approval';
        if (! empty($newlyAssignedEmpIds)) {
            $base_content = session('user_name').' has given Access to  approve the Leave or permission request placed by members in your team.';
            $base_content .= "<br/><br/><a href='".route('attendance.leave_approval_requests')."' target='_blank'>Click the Link</a> For accessing the page.<br/>";
            $this->SendNotificationIndividually($newlyAssignedEmpIds, $subject, $base_content, $notify_type);
        }
        if (! empty($unassignedEmpIds)) {
            $base_content = 'You no longer have Access to  approve the Leave or permission request placed by members in your team.<br/>'
                            .session('user_name').' has Removed you from approving.';
            $this->SendNotificationIndividually($unassignedEmpIds, $subject, $base_content, $notify_type);
        }

        return response()->json([
            'message' => 'Permit module updated successfully.',
        ]);
    }

    public function retrive_permission($emp_id, $module)
    {
        $assignedEmpIds = PermitModule::where('assigned_by', $emp_id)
            ->where('module_name', $module)
            ->pluck('emp_id')
            ->toArray();

        return response()->json([
            'permissions' => $assignedEmpIds,
        ]);
    }

    public function punch_card()
    {
        $LoadMultiselectchkbox = true;
        if (! PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
        $employees = PunchAttendance::select('employee_name', 'employee_code')
            ->groupBy('employee_name', 'employee_code')
            ->get();
        $LoadDateTimepicker = true;

        return view('attendance.punch_card', compact('employees', 'LoadDateTimepicker', 'LoadMultiselectchkbox'));
    }

    public function import_punch_card(Request $request)
    {

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);
        $file = $request->file('file');

        // Get the original filename
        $filename = $file->getClientOriginalName();

        // Initialize team type
        $teamType = $request->team_type;

        try {
            Excel::import(
                new AttendanceImport($request->work_mode, $teamType, $request->month),
                $request->file('file')
            );

            return redirect()->route('attendance.punch_card')->withSuccess('Attendance imported successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Invalid or corrupted Excel file: '.$e->getMessage()]);
        }

        // return response()->json(['message' => 'Attendance imported successfully']);
        // return redirect()->route('attendance.punch_card')->withSuccess('Attendance imported successfully');
    }

    /***Run Import as job in background. */
    /**public function import_punch_card(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);
        // Save the file permanently

    $storedPath = $request->file('file')->store('imports'); // stores in storage/app/private/imports
    $fullPath = Storage::path($storedPath); // resolves to storage/app/private/imports/...

    ImportPunchCardJob::dispatch($fullPath, $request->work_mode);


        return redirect()->route('attendance.punch_card')->withSuccess('Attendance import is being processed in the background.');
    }***/
    public function exportWeeklyAttendance(Request $request)
    {
        $query = PunchAttendance::orderBy('employee_name');

        if (! empty($request->start_date) && ! empty($request->end_date)) {
            $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d');
            $query->whereBetween('punch_date', [$startDate, $endDate]);
        }

        if (! empty($request->emp_name)) {
            $query->whereIn('employee_code', $request->emp_name);
        }

        if (! empty($request->work_mode)) {
            $selected_dates = PunchAttendance::where('status', $request->work_mode)
                ->when(! empty($request->emp_name), function ($q) use ($request) {
                    $q->where('employee_code', $request->emp_name);
                })
                ->select('punch_date')
                ->distinct()
                ->pluck('punch_date')
                ->toArray();

            $query->whereIn('punch_date', $selected_dates);
        }
        $query->orderBy('punch_date');
        $records = $query->get();
        if ($records->isEmpty()) {
            return redirect()->route('attendance.punch_card')->withError('No attendance records found for the selected criteria.');
        } else {
            $firstDate = $records->min('punch_date');
            $lastDate = $records->max('punch_date');

            $startDate = \Carbon\Carbon::parse($firstDate);
            $year = $startDate->format('Y');
            $month = $startDate->format('M'); // e.g., "Nov"
            $weekOfMonth = (int) ceil($startDate->day / 7); // e.g., 1st week

            $weekLabel = match ($weekOfMonth) {
                1 => '1st week',
                2 => '2nd week',
                3 => '3rd week',
                4 => '4th week',
                default => "{$weekOfMonth}th week"
            };
            $filename = "{$month}  {$weekLabel} Attn Report - {$year}(convtd).xlsx";

            return Excel::download(new AttendanceReportExport($records), $filename);
        }
    }
    public function generateReport(Request $request){
        $query = PunchAttendance::orderBy('employee_name');

        if (! empty($request->start_date) && ! empty($request->end_date)) {
            $startDate = Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d');
            $query->whereBetween('punch_date', [$startDate, $endDate]);
        }

        if (! empty($request->emp_name)) {
            $query->whereIn('employee_code', $request->emp_name);
        }

        if (! empty($request->work_mode)) {
            $query->where('status', $request->work_mode);
        }

        $records = $query->orderBy('punch_date')->get();

        if ($records->isEmpty()) {
            return response()->json(['table_html' => '<p class="text-danger">No records found.</p>']);
        }

        $table_html = view('attendance.partials.table', compact('records'))->render();

        return response()->json(['table_html' => $table_html]);
    }
public function updateStatus(Request $request)
{
    $attendance = PunchAttendance::findOrFail($request->id);
    $in_time = $attendance->checkin_time; //08:39:00
    $out_time = $attendance->checkout_time; //13:27:00

    // calculate worked duration if both times present
    $work_duration = null;
    if (! empty($in_time) && ! empty($out_time)) {
        try {
            $start = \Carbon\Carbon::parse($in_time);
            $end = \Carbon\Carbon::parse($out_time);
            if ($end->lessThan($start)) {
                // assume checkout on next day
                $end = $end->addDay();
            }
            $seconds = $end->diffInSeconds($start);
            // ensure non-negative seconds; guard against malformed times producing negative durations
            if ($seconds < 0) {
                $work_duration = null;
            } else {
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $secs = $seconds % 60;
                $work_duration = sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
            }
        } catch (\Exception $e) {
            $work_duration = null;
        }
    }
    $attendance->status = $request->status;
    if ($work_duration !== null) {
        $attendance->duration = $work_duration;
    }
    $attendance->save();

    return response()->json(['success' => "Status updated successfully."]);
}
    public function checkOutStatus($id)
    {
        $today = Carbon::today();
        $user = Attendance::whereDate('chkoutDate', $today)
            ->where('emp_id', $id)
            ->select('work_duration')
            ->first();

        if (! $user) {
            return response()->json(['checked_out' => false]);
        }

        return response()->json(['checked_out' => true]);
    }
}
