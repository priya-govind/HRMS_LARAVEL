<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $table = 'office_holidays';
    protected $fillable = ['holiday_name','from_dt','to_dt'];
}
