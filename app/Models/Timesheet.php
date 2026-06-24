<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
   protected $fillable = ['emp_id','create_dt','from_time','to_time','project_id','module_id','task_id','comments','custom_task','day','duration','custom_project','custom_module','editable'];
   protected $table = 'pm_timesheets';
    public function project() { return $this->belongsTo(Projects::class); }
    public function module()  { return $this->belongsTo(ProjectModule::class); }
    public function task()    { return $this->belongsTo(PMTasks::class); }
    public function employee(){
       return $this->belongsTo(User::class,'emp_id','id'); 
    }
   public function Projects(){
      return $this->belongsTo(Projects::class, 'project_id', 'id');
   }
}
