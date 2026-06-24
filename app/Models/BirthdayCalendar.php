<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthdayCalendar extends Model
{
    protected $table = 'birthday_calendar'; 
    protected $fillable = ['employee_name', 'employee_code', 'birth_date'];
}
