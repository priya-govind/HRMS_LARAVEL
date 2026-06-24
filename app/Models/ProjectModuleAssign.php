<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectModuleAssign extends Model
{
    protected $fillable = ['proj_id','module_id','emp_id'];
    protected $table = 'module_assign_members';

     public function projects()
    {
        return $this->belongsTo(Projects::class, 'proj_id');
    }
     public function module()
    {
        return $this->belongsTo(ProjectModule::class, 'module_id');
    }
    public function user() {
        return $this->belongsTo(User::class, 'emp_id', 'id');
    }
}
