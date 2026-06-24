<?php 
namespace App\Helpers;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityHelper {
    public static function logActivity($message,$log_name='default',$model = null, $properties = []) {
        $user = Auth::user(); // Get authenticated user
    
        // Check if a valid model instance is provided
        if ($model && $model instanceof \Illuminate\Database\Eloquent\Model) {
            
        $log = activity()
        ->useLog($log_name) 
        ->causedBy($user) // Associate log with user
        ->withProperties($properties) // Additional details
        ->performedOn($model)
        ->log($message);
            // Log that action was performed on this model
        } else{
        $log = activity()
        ->useLog($log_name) 
        ->causedBy($user) // Associate log with user
        ->withProperties($properties) // Additional details
        ->log($message); 
        }
    }
}
