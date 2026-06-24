<?php

namespace App\Http\Controllers;
use App\Helpers\PermissionHelper;
use Yajra\DataTables\Facades\DataTables;
use App\Models\NotifyType;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityHelper;
use Illuminate\Support\Str;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
   public function notify_type(Request $request){   
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
if ($request->ajax()) {
  $notify_typs = NotifyType::get();
  return DataTables::of($notify_typs)
       ->addIndexColumn()
       ->addColumn('action', function($row) {
          return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
            <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i></button>
           ';
      })
      ->rawColumns(['action'])
      ->make(true);
}
    return view('notifications.notify_type',['LoadDatatables' => true]);
   }
  
  public function store(Request $request)
  {
      $data = $request->validate([
          'notify_type' => 'required|unique:notify_type',
      ]);
      $data=$request->all();
      $data['notify_type']=$request->notify_type;

      // Mass assigment
      $notify = NotifyType::create($data);
      $log_name='notify_type';
      ActivityHelper::logActivity('Notification Type created',$log_name, $notify, [
        'request' => request()->all()
    ]);      
      return  response()->json(['success' => 'Notification Type details Added successfully!']);
  }

  public function edit($id){
    $notify_typs= NotifyType::find($id); 
    return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($notify_typs) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    
  }
  public function update(Request $request){
$notify=NotifyType::find($request->id);
    $log_name='notify_type';
    ActivityHelper::logActivity('Notification Type Edited',$log_name, $notify, [
      'request' => request()->all()
  ]);

    $data = $request->validate([
       'notify_type' => 'required|unique:notify_type,notify_type,'.$request->id,
        
    ]);
    $data=$request->all();
    $data['notify_type']=$request->notify_type;

    $notify->update($data);
//     $notify->is_active_cat = $data['is_active_cat'];
// $notify->save();

return  response()->json(['success' => 'Notification Type details updated successfully!']);
  }

public function destroy($id){
  $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
 if(!$cat_permission){ 
  return  response()->json(['message' => 'Not Authorized to see this page.'],200);
 }else{
  $user = Auth::user();
  $notify = NotifyType::find($id);
if ($notify) {
$log_name='notify_type';
     ActivityHelper::logActivity('Notification Type Deleted',$log_name, $notify, [
                'request' => request()->all()
            ]);
    $notify->delete();
}
  return response()->json(['message' => 'Record Deleted successfully!'],200);
  }
}
public function alert_notifications(Request $request){   
        if (!PermissionHelper::checkPermission('global.categories', $this->view_perm)) {
            // Redirect if permission is denied
            return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
if ($request->ajax()) {
   $notify_typs = Notification::where('receiver_id', session('user_id'))
                    ->select('subject', 'message','id')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($notification) {
                        $notification->message = Str::limit($notification->message, 50, '...');
                        return $notification;
                    });
  return DataTables::of($notify_typs)
       ->addIndexColumn()
        ->addColumn('message', function ($row) {
                        return strip_tags($row->message);
                    })
       ->addColumn('action', function($row) {
          return '<a href="' . route("notifications.view_notifications", $row->id) . '" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>';
      })
      ->rawColumns(['message','action'])
      ->make(true);
}
    return view('notifications.alert_notifications',['LoadDatatables' => true]);
   }
   public function view_notifications($id){
   
      $notify = Notification::find($id);

    if (!$notify) {
        return redirect()->route('dashboard')->withMessage('Notification not found.');
    }

    if ($notify->receiver_id!=session('user_id')) {
        return redirect()->route('dashboard')->withMessage('You are not Authorized to see this page.');
    }
    $notify->update(['is_read' => config('global.notify_read')]);

    return PermissionHelper::checkPermission('global.categories', $this->view_perm)
        ? view('notifications.view', compact('notify'))
        : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
      }
      public function make_read($id=''){
       $notify = Notification::where('receiver_id',session('user_id'));
       if(!empty($id)) {
        $notify->where('id',$id);
       }
       $res=$notify->update(['is_read' => config('global.notify_read')]);
        return $res;
      }
    public function CheckNotifications(){
              $notify = Notification::where('receiver_id', session('user_id'))
                                    ->where('is_read', config('global.notify_unread'))
                                    ->orderBy('id', 'desc')
                                    ->limit(1)
                                    ->get();
            $totalUnread = Notification::where('receiver_id', session('user_id'))
              ->where('is_read', config('global.notify_unread'))
              ->count();
                // Strip tags from the message field before returning
            $notify->transform(function ($item) {
              // $cleanMessage = strip_tags($item->subject);
                // Limit to first 5 words
              // $words = explode(' ', $cleanMessage);
                $item->message =$item->subject;
                // implode(' ', array_slice($words, 0, 100)) . '...';
                return $item;
            });
        return response()->json(['notify' => $notify,'un_read_cnt' => $totalUnread]);
    }
}
