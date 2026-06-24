<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Helpers\ActivityHelper;
use App\Events\NotifyInfo;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));


        ActivityHelper::logActivity('User Requests for Resetting Password','' ,'', [
            'request' => request()->all(),
            'status' => __($status)
        ]); 

    

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        
        // $request->validate([
        //     'email' => 'required|email',
        //     'password' => 'required|confirmed|min:8',
        //     'token' => 'required'
        // ]);
  
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password)
                ])->save();
                 $info['notify_type']="password_reset";
                 $info['user']=$user;
                 $info['mailer_id']=$user->email;
                event(new NotifyInfo($info));
            }
        );
        ActivityHelper::logActivity('User Resets Password','' ,'', [
            'request' => request()->all(),
            'status' => __($status)
        ]);
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}