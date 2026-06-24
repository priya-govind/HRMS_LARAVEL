<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Roles;
use App\Models\TeamType;
use App\Models\BirthdayCalendar;
use App\Imports\BirthdayImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;



use Illuminate\Support\Facades\Auth;

class CommonController extends Controller
{

    public function show_profile(){
    $cu_user = Auth::user();
    $user=User::find($cu_user->id);
    $roles=Roles::all();
    $userRoles = $user->roles->pluck('id')->toArray();
        return view('my_profile', compact('user','roles','userRoles'));
    }
public function myprofile_update(Request $request) {
    $user = Auth::user();

    $request->validate([
        'name' => 'required|regex:/^[a-zA-Z0-9\s]+$/|unique:users,name,' . $user->id,
        'email' => 'required|unique:users,email,' . $user->id . '|email',
        'password' => 'nullable|min:8|required_with:confirm_password|same:confirm_password',
        'confirm_password' => 'nullable|min:8',
        'gender' => 'required',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'address' => 'required',
    ]);
    $data = $request->only(['name', 'email', 'password', 'gender', 'address']);
    // Handle image upload
    if ($request->hasFile('image')) {
        if ($user->image) {
            $oldImagePath = public_path('images/' . $user->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('images'), $imageName);
        $data['image'] = $imageName;
    }
$user = User::find($user->id); // Retrieve user
$user->update($data);
  
 
    return redirect()->route('dashboard')->withSuccess('Profile Updated successfully!');
}
public function generate_worklog_report(){
        $teamTypes = TeamType::with([
                        'reportingPerson',
                        'teams.teamMembers.user.roles'
                    ])->get();

        $result = $teamTypes->map(function ($type) {
                        return [
                            'id' => $type->id,
                            'team_type' => $type->id,
                           // 'team_name' => $type->teams->pluck('team_name')->implode(', '), // or handle individually
                            'reporting_person' => $type->reportingPerson
                                ? [
                                    'id' => $type->reportingPerson->id,
                                    'name' => $type->reportingPerson->name,
                                    'email' => $type->reportingPerson->email,
                                ]
                                : null,
                            'team_members' => $type->teams->flatMap(function ($team) {
                                return $team->teamMembers->map(function ($member) {
                                    return [
                                        'id' => optional($member->user)->id,
                                        'name' => optional($member->user)->name,
                                        'email' => optional($member->user)->email,
                                    ];
                                });
                            })->unique('id')->values()->toArray()
                        ];
                    });

        dd($result);
}
public function birthday_remainder(Request $request)
{
    $LoadDatatables=true; 
    if ($request->isMethod('post')) {
        $request->validate([
            'birthday_file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new BirthdayImport(), $request->file('birthday_file'));
            return redirect()->route('birthday_remainder')->with('success', 'Birthday Calendar imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('birthday_remainder')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    return view('birthday_remainder',compact('LoadDatatables'));
}
public function birthday_remainder_list(Request $request){
    $birthdays=BirthdayCalendar::get();
     return DataTables::of($birthdays)
      ->addIndexColumn()
      ->addColumn('birth_date', function($row) {
            return Carbon::parse( $row->birth_date)->format('d M,Y');
        })
       ->make(true);
}
// public function check_birthday_alert(Request $request)
// {
//     $today = Carbon::today();
//     $dayOfWeek = $today->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

//     // Determine the next working day
//     switch ($dayOfWeek) {
//         case Carbon::FRIDAY: // 5
//             $checkDate = $today->copy()->addDays(3); // Friday → Monday
//             break;
//         case Carbon::THURSDAY: // 4
//             $checkDate = $today->copy()->addDay(); // Thursday → Friday
//             break;
//         case Carbon::WEDNESDAY: // 3
//             $checkDate = $today->copy()->addDay(); // Wednesday → Thursday
//             break;
//         case Carbon::TUESDAY: // 2
//             $checkDate = $today->copy()->addDay(); // Tuesday → Wednesday
//             break;
//         case Carbon::MONDAY: // 1
//             $checkDate = $today->copy()->addDay(); // Monday → Tuesday
//             break;
//         default:
//             // For Saturday (6) or Sunday (0), default to Monday
//             $checkDate = $today->copy()->next(Carbon::MONDAY);
//             break;
//     }

//     // Avoid duplicate alerts
//     if ($request->session()->get('birthday_alert_shown') === $checkDate->toDateString()) {
//         return response()->json('');
//     }

//     $birthdays = BirthdayCalendar::whereDay('birth_date', $checkDate->day)
//         ->whereMonth('birth_date', $checkDate->month)
//         ->where(function ($query) use ($today) {
//             $query->whereNull('last_alerted_date')
//                   ->orWhereDate('last_alerted_date', '!=', $today->toDateString());
//         })
//         ->get();

//     if ($birthdays->isNotEmpty()) {
//         foreach ($birthdays as $employee) {
//             $employee->last_alerted_date = $today;
//             $employee->save();
//         }

//         $names = $birthdays->pluck('employee_name')->implode(', ');
//         $message = $names . ' have birthday on ' . $checkDate->format('l') . '!';

//         $request->session()->put('birthday_alert_shown', $checkDate->toDateString());

//         return response()->json($message);
//     }

//     return response()->json('');
// }


public function check_birthday_alert(Request $request){
    $today = Carbon::today();
    $upcomingBirthdays = collect();

    $daysChecked = 0;
    $i = 0;


    while ($daysChecked < 5) {
        $checkDate = $today->copy()->addDays($i);
        $current_date= Carbon::today()->format('Y-m-d');
        // Always check birthdays (even weekends)
        $birthdays = BirthdayCalendar::whereDay('birth_date', $checkDate->day)
            ->whereMonth('birth_date', $checkDate->month)
            ->where(function ($query) use ($current_date) {
                $query->whereNull('last_alerted_date')
                      ->orWhereDate('last_alerted_date', '!=', $current_date);
            })
            ->get();

        if ($birthdays->isNotEmpty()) {
               foreach ($birthdays as $employee) {
                            $employee->last_alerted_date = $today;
                            $employee->save();
                        }
                        
            $day = $checkDate->day;
            $suffix = $this->getOrdinalSuffix($day);
            $formatted_date = $day . $suffix . ' ' . $checkDate->format('F');

            $upcomingBirthdays->push([
                'date' => $formatted_date,
                'day' => $checkDate->isSameDay($today) ? 'Today' : $checkDate->format('l'),
                'names' => $birthdays->pluck('employee_name')->implode(', ')
            ]);
        }

        // Only increment working day counter if not weekend
        if (!$checkDate->isWeekend()) {
            $daysChecked++;
        }

        $i++;
    }

    if ($upcomingBirthdays->isNotEmpty()) {
        return response()->json($upcomingBirthdays);
    }

    return response()->json([]);
}
function getOrdinalSuffix($day)
{
    if (!in_array(($day % 100), [11, 12, 13])) {
        switch ($day % 10) {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
        }
    }
    return 'th';
}

}
