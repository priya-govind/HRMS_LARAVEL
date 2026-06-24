<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingMode extends Model
{
      //use SoftDeletes;
    protected $table = 'working_mode';
    protected $fillable = ['work_mode_name','mode_status'];
}
