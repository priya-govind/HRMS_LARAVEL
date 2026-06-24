<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Roles;
use App\Models\TeamMembers;
use App\Models\Teams;
use App\Models\TeamType;
use App\Models\Attendance;
use App\Models\Timesheet;
use App\Models\Leaveinfo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use App\Helpers\ActivityHelper;



class AuthController extends Controller
{
    public function index(){
        $roles=Roles::all();
        return Auth::check() ? redirect() ->route('dashboard') : view('login',compact('roles'));
    }
    public function home(){
        return Auth::check() ? redirect() ->route('dashboard') :  redirect("login")->withSuccess('Please log in');
    }
    public function checkRoles(Request $request)
    {
        $email = $request->email;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['status' => 'not_found']);
        }

        $roles = DB::table('roles_user')
            ->join('roles', 'roles.id', '=', 'roles_user.roles_id')
            ->where('roles_user.user_id', $user->id)
            ->select('roles.id', 'roles.role_name')
            ->get();

        return response()->json([
            'status' => 'found',
            'roles' => $roles,
            'count' => $roles->count()
        ]);
    }
    public function login(Request $request)
    {
        session()->flush();
    
        // Validate credentials
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');
    
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user(); // Get the authenticated user
            if( $user->emp_status==config('global.inactive_status')){
                 Auth::logout();
            session()->invalidate();

            ActivityHelper::logActivity('In Active User try to loggin.','' , $user, [
                'request' => request()->all()
            ]);
            return back()->withError('Your Account is Inactive. Contact HR for more details.');
            }
            // Retrieve the specific role by `role_id` if provided
            if ($request->role_id) {
                /** @var \App\Models\User $user */
                $chkout_flag=0;
                $role = $user->roles()->where('roles.id', $request->role_id)->first(); // Simplified role retrieval
               
                if ($role) {
        
                    // Store relevant role information in the session
                    session([
                        'user_name' => $user->name,
                        'user_id' => $user->id,
                        'role_name' => $role->role_name,
                        'role_id' => $role->id,
                        'dp_image' => $user->image,
                        //'team_id'=>(array) $teams,
                        'email'=> $user->email,
                        'support_access' => $user->support_access,
                        // Dynamically set folder for redirection
                    ]);
                }
                      // Check role condition and set session
                   if (in_array(session('role_id'), config('global.first_level_role'))) {
                        $team_type = TeamType::where('pm_id', session('user_id'))->pluck('id')->toArray();
                        $teams = Teams::whereIn('team_type', $team_type)->pluck('id')->toArray();
                        session(['team_id' => (array) $teams]);
                        session(['team_type' => (array) $team_type]);
                    } else {
                        $teams = TeamMembers::where('emp_id', $user->id)->pluck('team_id')->toArray();
                        session(['team_id' => (array) $teams]);
                    }
                    $flag_attend=   false;  
                    $birth_alert = false;
                    $attendance = Attendance::where('emp_id', session('user_id'))
                                ->whereDate('chkinDate', Carbon::today())
                                ->select('chkinDate','timesheet_slot')
                                ->first();
                    $isleave=Leaveinfo::where('emp_id', session('user_id'))
                                        ->whereDate('from_dt', '<=',  Carbon::today())
                                        ->whereDate('to_dt', '>=',  Carbon::today())
                                        ->where('leave_status',config('global.leave_approved'))
                                        ->first();
                            //logger()->info('inputs', ['isleave' => $isleave]);
                                            if ($isleave) {
                                                // logger()->info('inputs', ['isleave' => $isleave]);
                                                $isleave->update(['leave_status' => config('global.leave_cancelled')]);
                                                $notify_type="leave_cancel";
                                                    $subject="Fortgrid - Leave Cancelled ".session('user_name');
                                                    $message="Following user have applied for leave but logged in for the day. The details are as follows:<br/>";
                                                    $message.="<br/>Name:".session('user_name');
                                                    $message.="<br/><br/><a href='" . route('attendance.attendance_info') . "' target='_blank'>Click the Link For more details.</a><br/>";
                                                        
                                                    $this->AttendanceNotification($notify_type,$subject,$message);
                                            } 
                                            // else {
                                            //     // logger()->info('check User will Login', ['isleave' => 'no']);
                                            // }
                                if($attendance!=''){
                                        $chkin_time=date('H:i:s', strtotime($attendance->chkinDate));
                                        $flag_attend=   true;
                                        session(['chkin_time' => $chkin_time,
                                                'timeslots' => $attendance->timesheet_slot,
                                    ]);
                                } 
                                if((in_array(session('role_id'),config('global.role_without_attendance')))){
                                    $flag_attend=   true;
                                }
                                if (session('role_id') == config('global_roles.HR')) {
                                        $birth_alert = true;
                                    }
                                session(['checked_attendance' => $flag_attend]);
                                session(['show_birthday_alert' => $birth_alert]);

                                    /**Incomplete checkout and timesheet */
                                if (!in_array(session('role_id'), config('global.role_without_attendance'))) {
                                    $attn_info=Attendance::where('emp_id',session('user_id'))->orderBy('id','desc')->first();
                                    $chkout_flag=0;
                                        if($attn_info){
                                        $chkout_flag=($attn_info->chkoutDate==null ||  $attn_info->chkoutDate!='0000-00-00 00:00:00') ? 1 : 0;
                                        session(['irregular_chkout' => $chkout_flag]);
                                            session(['last_checkin_dt' => $attn_info->chkinDate->format('d-m-Y')]);
                                            session(['last_checkin_time' => $attn_info->chkinDate ? $attn_info->chkinDate->format('H:i:s') : 'N/A']);
                                            $recorded_dt=$attn_info->chkinDate->format('Y-m-d');
                                                $query_timeslot = Timesheet::where('emp_id', $user->id)
                                                                             ->whereDate('create_dt', $recorded_dt);
                                                                             
                                                   $firstSlot   = $query_timeslot->first();   // First matching record
                                                   $filledSlots = $query_timeslot->count(); 
                                                    $timesheet_flag=($filledSlots!=$attn_info->timesheet_slot) ? 1 : 0;
                                                    session(['incomplete_timesheet'=> $timesheet_flag]);
                                                    session(['chkin_time' => $attn_info->chkinDate->format('H:i:s'),
                                                                'timeslots' => $attn_info->timesheet_slot,
                                                    ]);
                                                    //dd(session()->all());
                                                    // if(session('incomplete_timesheet')==true &&  $firstSlot->id!=''){
                                                    //     return redirect()->route('edit_timesheet', ['id' => $firstSlot->id]);
                                                    // }
                                        }
                                    }
                                    $waiver_set=(in_array(session('role_id'),config('global.restriction_free_roles')))? 1 :0;
                                    session(['irregular_chkout' => $chkout_flag]);
                                    session(['waiver_set' => $waiver_set]);
                                     /**Incomplete checkout and timesheet */



                                //dd(session()->all());
                                    ActivityHelper::logActivity('User logged in','', $user, [
                                        'request' => request()->all()
                                    ]);
                                return redirect() ->route('dashboard')->withSuccess('Welcome, ' . $role->role_name);
            }
        }
          Auth::logout();
            session()->invalidate();
            ActivityHelper::logActivity('User try to loggin.Role not recognized.', '', Auth::user(), [
                'request' => request()->all()
            ]);
            return back()->withError('Role not recognized.');
    }

public function logout(Request $request){

    ActivityHelper::logActivity('User logs out!','' ,'', [
        'session_data' => session()->all()
    ]); 

    Auth::logout(); 
    $request->session()->invalidate(); 
    $request->session()->regenerateToken(); 
    return redirect('/');
}
public function checkout(Request $request)
{
    $userId = session('user_id');
    $today = Carbon::today();
    $now = Carbon::now();

    $attendance = Attendance::where('emp_id', $userId)
        ->whereDate('chkinDate', $today)
        ->first();

    if (!$attendance) {
        return response()->json(['message' => 'No check-in record found.', 'redirect_url' => '/dashboard']);
    }

    $expectedSlots = $attendance->timesheet_slot ?? 0;
    $filledSlots = Timesheet::where('emp_id', $userId)
        ->whereDate('create_dt', $today)
        ->count();

    if ($attendance->chkoutDate !== null) {
        return response()->json(['checkout' =>true,'message' => 'You have already checked out.', 'redirect_url' => '/dashboard']);
    }
    if (($filledSlots < $expectedSlots) && (!in_array(session('role_id'),config('global.mgmt_team')))) {
        return response()->json(['message' => 'Complete the Time Sheet to Checkout.', 'redirect_url' => '/timesheet_log']);
    }
    // Early checkout check
    $sixPM = Carbon::today()->setHour(18)->setMinute(0);
    
$now = Carbon::now();
$currentDate = $now->toDateString();
$currentTime = $now->format('g:i A');

// Define 4 PM and 6 PM as Carbon instances
$fourPM = Carbon::createFromTime(16, 0, 0);
$sixPM = Carbon::createFromTime(18, 0, 0);

// Check if permission exists between 4 PM and 6 PM
$permissionExists = LeaveInfo::where('leave_type', config('global.leave_type_permission'))
    ->where('from_dt', $currentDate)
    ->whereTime('from_time', '<=', $sixPM->toTimeString())
    ->whereTime('to_time', '>=', $fourPM->toTimeString())
    ->exists();
// Detect early checkout only if it's before 6 PM, no permission between 4–6, and no early_reason
/*if ($now->lt($sixPM) && !$permissionExists && !$request->has('early_reason') && (!in_array(session('role_id'),config('global.mgmt_team')))) {
    return response()->json([
        'message' => 'Apply for permission.Contact Project manager or project Leader for more Details.',
        'early_checkout' => true
    ]);
}*/
    // Save early checkout reason if provided
    if ($request->has('early_reason')) {
        $attendance->early_checkout_reason = $request->input('early_reason');

         $inputTime=$now;
         $inputCarbon = Carbon::parse($inputTime);
         $comparisonTime = Carbon::today()->setTime(6, 00);

                if ($inputCarbon->gt($comparisonTime)) {
                   $notify_type="attendance";
                   $subject="Fortgrid - Early Check out by ".session('user_name');
                   $base_content="Following user have logged out early. The details are as follows:";
                    $base_content.="<br/><br/>Name:".session('user_name');
                   $base_content.="<br/>Logout Time:".$inputTime.'<br/>';
                    $base_content.="<br/>Reason:".$request->input('early_reason').'<br/>';
                   $this->AttendanceNotification($notify_type,$subject,$base_content);    
                }
    }
    /**Update Current time as the last entry in Timesheet */
        $timesheet = Timesheet::where('emp_id', $userId)
                            ->whereDate('create_dt', $today)
                            ->orderBy('id', 'desc')
                            ->select('from_time','id')
                            ->first();
                           
            if ($timesheet) {
                $exact_fromTime = Carbon::createFromFormat('g:i A', $timesheet->from_time);
                $durationMinutes = $exact_fromTime->diffInMinutes(
                    Carbon::createFromFormat('g:i A', $currentTime),
                    false
                );
                $timesheet->update([
                    'to_time'  => $currentTime,
                    'duration' => $durationMinutes,
                ]);
            }
           // dd($timesheet->fresh());

    // Calculate work duration and save checkout
                $minutesWorked = Carbon::parse($attendance->chkinDate)->diffInMinutes($now);
                $attendance->work_duration = $minutesWorked;
                $attendance->chkoutDate = $now;
                $attendance->save();
    return response()->json(['checkout' => true,'message' => 'You Have Checked Out Successfully!', 'redirect_url' => '/dashboard']);
}
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
     

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required'
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
public function update_checkout_status(Request $request)
{
    $leave_type = ($request->leave_type == 1) ? 'leave' : 'permission';
    $checkoutTime = Carbon::parse(now()); // e.g. 12:34 PM
    $date = now()->format('Y-m-d');

    // Attendance record for login time
    $attendance = Attendance::where('emp_id', session('user_id'))
        ->whereDate('chkinDate', $date)
        ->first();
    $loginTime = Carbon::parse($attendance->chkinDate);

    // Last entry for today
    $lastEntry = Timesheet::where('emp_id', session('user_id'))
        ->whereDate('create_dt', $date)
        ->orderBy('to_time', 'desc')
        ->first();

    $slots = [
        "9:00am - 10:00am", "10:00am - 10:45am", "11:00am - 12:00pm",
        "12:00pm - 1:00pm", "1:30pm - 2:00pm", "2:00pm - 3:00pm",
        "3:00pm - 4:00pm", "4:15pm - 5:00pm", "5:00pm - 6:00pm"
    ];

    if ($lastEntry) {
        $entryFrom = Carbon::parse($lastEntry->from_time);
        $entryTo   = Carbon::parse($lastEntry->to_time);

        // Case A: Adjust last entry if it overlaps checkout
        if ($checkoutTime->between($entryFrom, $entryTo)) {
            $lastEntry->to_time = $checkoutTime->format('g:i A');
            $lastEntry->duration = $entryFrom->diffInMinutes($checkoutTime);
            $lastEntry->save();

            // Add permission for remaining part of slot
            Timesheet::create([
                'emp_id'        => session('user_id'),
                'create_dt'     => $date,
                'day'           => Carbon::parse($date)->dayName,
                'from_time'     => $checkoutTime->format('g:i A'),
                'to_time'       => $entryTo->format('g:i A'),
                'custom_task'   => $leave_type,
                'custom_project'=> $leave_type,
                'custom_module' => $leave_type,
                'comments'      => $request->earlyReasonInput,
                'status'        => $leave_type,
                'editable'      => 2,
            ]);
        }
    } else {
// Case B: No last entry → fill blanks from login to checkout
        foreach ($slots as $slot) {
            [$from, $to] = explode('-', $slot);
            $fromTime = Carbon::parse(trim($from));
            $toTime   = Carbon::parse(trim($to));

            // Case 1: login falls inside this slot
            if ($loginTime->between($fromTime, $toTime) && $checkoutTime->gte($fromTime)) {
                Timesheet::create([
                    'emp_id'        => session('user_id'),
                    'create_dt'     => $date,
                    'day'           => Carbon::parse($date)->dayName,
                    'from_time'     => $loginTime->format('g:i A'),
                    'to_time'       => min($checkoutTime, $toTime)->format('g:i A'),
                    'custom_task'   => '-',
                    'custom_project'=> '-',
                    'custom_module' => '-',
                    'comments'      => '-',
                ]);
            }

            // Case 2: slot fully after login and ends before checkout
            elseif ($fromTime->gte($loginTime) && $toTime->lte($checkoutTime)) {
                Timesheet::create([
                    'emp_id'        => session('user_id'),
                    'create_dt'     => $date,
                    'day'           => Carbon::parse($date)->dayName,
                    'from_time'     => trim($from),
                    'to_time'       => trim($to),
                    'custom_task'   => '-',
                    'custom_project'=> '-',
                    'custom_module' => '-',
                    'comments'      => '-',
                ]);
            }

            // Case 3: checkout falls inside this slot → split
            elseif ($checkoutTime->between($fromTime, $toTime)) {
                Timesheet::create([
                    'emp_id'        => session('user_id'),
                    'create_dt'     => $date,
                    'day'           => Carbon::parse($date)->dayName,
                    'from_time'     => trim($from),
                    'to_time'       => $checkoutTime->format('g:i A'),
                    'custom_task'   => '-',
                    'custom_project'=> '-',
                    'custom_module' => '-',
                    'comments'      => '-',
                ]);
            }
        }
    }
// Case C: Permission entries after checkout until 6 PM
foreach ($slots as $slot) {
    [$from, $to] = explode('-', $slot);
    $fromTime = Carbon::parse(trim($from));
    $toTime   = Carbon::parse(trim($to));

    // Case 1: Checkout falls inside this slot → split
    if ($checkoutTime->between($fromTime, $toTime)) {
        Timesheet::create([
            'emp_id'        => session('user_id'),
            'create_dt'     => $date,
            'day'           => Carbon::parse($date)->dayName,
            'from_time'     => $checkoutTime->format('g:i A'),
            'to_time'       => $toTime->format('g:i A'),
            'custom_task'   => $leave_type,
            'custom_project'=> $leave_type,
            'custom_module' => $leave_type,
            'comments'      => $request->earlyReasonInput,
            'status'        => $leave_type,
            'editable'      => 2,
        ]);
    }

    // Case 2: Slots fully after checkout → full permission
    if ($fromTime->gte($checkoutTime)) {
        Timesheet::create([
            'emp_id'        => session('user_id'),
            'create_dt'     => $date,
            'day'           => Carbon::parse($date)->dayName,
            'from_time'     => trim($from),
            'to_time'       => trim($to),
            'custom_task'   => $leave_type,
            'custom_project'=> $leave_type,
            'custom_module' => $leave_type,
            'comments'      => $request->earlyReasonInput,
            'status'        => $leave_type,
            'editable'      => 2,
        ]);
    }
}

    // Attendance update
    $userId = session('user_id');
    $today = Carbon::today();
    $now = Carbon::now();

    $attendance = Attendance::where('emp_id', $userId)
        ->whereDate('chkinDate', $today)
        ->first();

    $minutesWorked = Carbon::parse($attendance->chkinDate)->diffInMinutes($now);
    $attendance->work_duration = $minutesWorked;
    $attendance->chkoutDate = $now;
    $attendance->save();

    $notify_type="attendance";
    $subject="Fortgrid - Early Checkout by ".session('user_name');
    $message="Following user has checked out early and mentioned as ".$leave_type.". The details are as follows:<br/>";
    $message.="<br/>Name:".session('user_name');
    $message.="<br/>Checkout Time:".now()->format('g:i A');
    $message.="<br/>Reason:".$request->input('earlyReasonInput').'<br/>';
    $message.="<br/>Notification Date:".date('jS F, Y', strtotime(now())).'<br/>';
    $this->AttendanceNotification($notify_type,$subject,$message);

    return response()->json(['success' => 'You Have Checked Out Successfully!']);
}

          
}
