<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class TaskAssignEmp extends Model
{
    use SoftDeletes;

    protected $table = 'task_assign_emp'; 
    protected $dates = ['deleted_at']; 
    protected $fillable = ['task_id', 'employee_id', 'task_info', 'updated_at','emp_task_status','comments','ctrl_status'];



    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'emp_task_status', 'id'); 
    }
    public function user() {
        return $this->belongsTo(User::class, 'employee_id', 'id'); // Link employee_id to User table
    }
    public function project(){
        return $this->belongsTo(Projects::class, 'proj_id', 'id');
    }
      public function getTeamId($task_id)
    {
        // Fetch the team assigned to the given task_id
        $taskTeam = TaskAssignTeam::where('task_id', $task_id)->first();
        
        if ($taskTeam) {
            // Check if the employee is part of the fetched team
            $teamMember = TeamMembers::where('team_id', $taskTeam->team_id)
                                     ->where('emp_id', $this->employee_id)
                                     ->first();
            if ($teamMember) {
                return $teamMember->team_id;
            }
        }

        return null;
    }
    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id', 'id'); 
    }
    public function taskStatusTeam()
{
    return $this->hasOne(TaskStatusTeam::class, 'task_id')
        ->whereIn('team_id', session('team_id'))
        ->with('status'); // Ensure status relationship exists
}
public function assignedEmployees() {
    return $this->hasMany(TaskAssignEmp::class, 'task_id');
}
}
