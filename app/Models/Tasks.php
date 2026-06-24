<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tasks extends Model
{
    use SoftDeletes;

    protected $fillable = ['task_name','proj_id','proj_typ_id','team_typ_id','startDate','endDate','comments'];
    protected $table = 'tasks';

    public function assignedMembers() {
        return $this->belongsToMany(User::class,'task_assign_emp','task_id','employee_id')
                ->where('emp_status',config('global.active_status'))
                 ->where('employee_id','!=', session('user_id'))
                    ->wherePivot('deleted_at', null)
                    ->wherePivot('ctrl_status','!=', config('global.ctrl_status')) 
                    ->withPivot('task_info','team_id', 'comments','id','emp_task_status');
    }
    public function assignedTeams()
    {
        return $this->belongsToMany(Teams::class, 'task_assign_team', 'task_id', 'team_id')
                                ->select('teams.id', 'teams.team_name'); 
        
    }
   public function project()
{
    return $this->belongsTo(Projects::class, 'proj_id', 'id');
}

public function status()
{
    return $this->belongsTo(ProjectStatus::class, 'task_status', 'id');
}
public function myAssignedInfo()
{
    return $this->hasOne(TaskAssignEmp::class, 'task_id', 'id')
                ->where('employee_id', session('user_id'));
}

public function teamTaskStatus()
{
    $result= $this->hasOne(TaskStatusTeam::class, 'task_id');
    // if(!empty(session('team_id'))){
    //     $result->whereIn('team_id', session('team_id'));
    // }
        $result->with('status');
        return $result;
}
public function teamTaskStatus_TL()
{
    $result= $this->hasOne(TaskStatusTeam::class, 'task_id');
    if(!empty(session('team_id'))){
        $result->whereIn('team_id', session('team_id'));
    }
        $result->with('status');
        return $result;
}
public function reporting_employees()
{
    return $this->belongsToMany(User::class, 'task_assign_emp', 'task_id', 'employee_id')
        ->where('task_assign_emp.ctrl_status', 1)
        ->select('users.name'); // Select only the employee names
}
public function task_user()
{
    return $this->belongsTo(User::class, 'employee_id', 'id')
        ->select('id', 'name');
}
public function task_owner()
{
    return $this->belongsTo(User::class, 'created_by', 'id')
        ->select('id', 'name');
}
public function empTaskStatus()
{
    return $this->hasOne(TaskAssignEmp::class, 'task_id')
                ->where('employee_id', session('user_id'))
                ->with('status') // eager load the ProjectStatus model
                ->withDefault(); // ensures no "trying to get property of null" error
}
public function assignedEmployees()
{
    return $this->hasMany(TaskAssignEmp::class, 'task_id');
}
public function assignedNormalEmployees()
{
    return $this->hasMany(TaskAssignEmp::class, 'task_id')  ->where('ctrl_status','!=',1);
}
public function teamStatus()
{
    return $this->hasMany(TaskStatusTeam::class, 'task_id');
}

 
}
