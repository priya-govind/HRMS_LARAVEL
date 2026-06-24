<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStatusTeam extends Model
{
     protected $fillable = ['task_id','team_id','team_status'];
    protected $table = 'task_status_team';

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'team_status', 'id');
    }
    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }
    
}
