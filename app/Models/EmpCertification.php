<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpCertification extends Model
{
    protected $table = 'emp_certification';
    protected $fillable = ['user_id','certification','cer_image'];
}
