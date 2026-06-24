<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectModule extends Model
{
    protected $fillable = ['module_name','proj_id','desc'];
    protected $table = 'proj_modules';
    public $timestamps = false;

    public function projects()
    {
        return $this->belongsTo(Projects::class, 'proj_id');
    }
    public function pm_tasks() {
        return $this->hasMany(PMTasks::class, 'module_id', 'id');
    }
     public function timesheets() {
        return $this->hasMany(Timesheet::class, 'module_id');
    }
}
