<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PunchAttendance extends Model
{
   protected $table = 'punch_attendance'; 
    protected $fillable = ['punch_date', 'employee_name', 'employee_code', 'checkin_time','checkout_time','status','duration'];
}
