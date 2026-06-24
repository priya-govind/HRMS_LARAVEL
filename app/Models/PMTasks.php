<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PMTasks extends Model
{
   protected $fillable = ['task_name','project_id','module_id','endDate','task_status','created_by','task_desc','comments'];
    protected $table = 'pm_tasks';

    public function project(){
        return $this->belongsTo(Projects::class, 'project_id', 'id');
    }
    public function pm_task_status(){
        return $this->belongsTo(ProjectStatus::class, 'task_status', 'id');
    }
    public function modules(){
        return $this->belongsTo(ProjectModule::class, 'module_id', 'id');
    }
     // Relationship to assignment table
    public function assignedEmployees(){
        return $this->hasMany(PMTasksAssign::class, 'task_id');
    }
    public function taskDocs(){
        return $this->hasMany(TaskFileUploads::class, 'task_id');
    }
    // Accessor to get all employee names separated by commas
    public function getAssignedEmployeeNamesAttribute(){
        return $this->assignedEmployees
            ->map(function ($assign) {
                return $assign->employee->name;   // assuming relation in PMTasksAssign → employee()
            })
            ->implode(', ');
    }
    public function getAssignedEmployeeDetailsAttribute(){
        return $this->assignedEmployees
            ->map(function ($assign) {
                return [
                    'id'   => $assign->employee->id,
                    'name' => $assign->employee->name,
                    'email' => $assign->employee->email,
                    'role' => $assign->employee->roles->pluck('role_name')->first(),
                ];
            });
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function timesheets() {
        return $this->hasMany(Timesheet::class, 'task_id');
    }
}
