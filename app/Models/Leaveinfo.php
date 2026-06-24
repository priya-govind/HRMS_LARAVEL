<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaveinfo extends Model
{
    protected $table = 'leave_info';
    protected $fillable = ['emp_id','from_dt','to_dt','leave_type','from_time','to_time','reason','approved_by','leave_status','reason_status'];

    public static function getLeavesForPM($pmId){
        return self::whereIn('emp_id', function ($query) use ($pmId) {
            $query->select('team_members.emp_id')
                ->from('team_members')
                ->join('teams', 'teams.id', '=', 'team_members.team_id')
                ->join('team_type', 'team_type.id', '=', 'teams.team_type')
                ->where('team_type.pm_id', $pmId);
        })->get();
    }
    public function emp_name()
        {
            return $this->belongsTo(User::class, 'emp_id','id');
        }
    
}
