<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PMTasksAssign extends Model
{
    protected $fillable = ['task_id','employee_id','emp_comments'];
    protected $table = 'pm_task_assign_emp';

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
     public function empStatus()
    {
        return $this->belongsTo(ProjectStatus::class, 'emp_task_status', 'id');
    }
    public function task()
    {
        return $this->belongsTo(PMTasks::class, 'task_id','id');
    }
    public function taskDocs(){
        return $this->hasMany(TaskFileUploads::class, 'task_id','task_id');
    }

}
