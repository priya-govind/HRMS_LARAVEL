<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAssignTeam extends Model
{
  protected $table = 'task_assign_team';

   public function teamMembers()
    {
        return $this->hasMany(TeamMembers::class, 'team_id', 'team_id');
    }

}
