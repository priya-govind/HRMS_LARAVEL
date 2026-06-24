<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projects extends Model
{
    use SoftDeletes;
    protected $fillable = ['proj_name','proj_desc','start_date','end_date','proj_status','proj_color'];
    protected $table = 'projects';

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'proj_status');
    }
    public function tasks() {
        return $this->hasMany(Tasks::class, 'proj_id', 'id');
    }
    public function projtype()
    {
        return $this->belongsTo(ProjectType::class, 'proj_type');
    }
    public function assignments()
    {
        return $this->hasMany(ProjectModuleAssign::class, 'proj_id');
    }
    public function pm_tasks() {
        return $this->hasMany(PMTasks::class, 'project_id', 'id');
    }
    public function modules() {
        return $this->hasMany(ProjectModule::class, 'proj_id');
    }
    public function timesheets() {
        return $this->hasMany(Timesheet::class, 'project_id');
    }
}

    

