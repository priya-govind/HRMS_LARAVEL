<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermitModule extends Model
{
     protected $table = 'permit_module';
    protected $fillable = ['emp_id','assigned_by','module_name'];
}
