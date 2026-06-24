<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpDocs extends Model
{
    protected $fillable = ['user_id ','image_path','emp_qualification','emp_marks'];
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'emp_docs', 'user_id')
            ->withPivot('image_path', 'emp_qualification', 'emp_marks')
            ->withTimestamps();
    }


}
