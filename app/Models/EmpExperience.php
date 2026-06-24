<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpExperience extends Model
{
    protected $table = 'emp_experience';
    protected $fillable = ['user_id ','company_name','yr_experience'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'emp_experience', 'user_id')
            ->withPivot('company_name', 'yr_experience')
            ->withTimestamps();
    }
}
