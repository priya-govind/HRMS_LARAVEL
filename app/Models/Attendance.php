<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    //use SoftDeletes;
    protected $table = 'attendance';
    protected $fillable = ['chkinDate','chkoutDate','emp_id','work_duration','working_mode','comments','timesheet_slot','late_checkout_reason','sys_problem'];
    protected $casts = [
                        'chkinDate'  => 'datetime',
                        'chkoutDate' => 'datetime',
                       ];

    public function employee(){
        return $this->belongsTo(User::class, 'emp_id','id');
    }
    public function workingMode(){
        return $this->belongsTo(WorkingMode::class, 'working_mode');
    }
     public function user()
    {
        return $this->belongsTo(User::class, 'emp_id');
    }


}
