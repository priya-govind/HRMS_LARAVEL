<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TeamType;

class Teams extends Model
{
    use SoftDeletes;
    protected $fillable = ['team_type','team_name','proj_type'];
    protected $table = 'teams';
 
    public function teamType(){
        return $this->belongsTo(TeamType::class, 'team_type');
    }
     public function projType(){
        return $this->belongsTo(ProjectType::class, 'proj_type');
    }
    public function members() {
        return $this->belongsToMany(User::class);
    }
    public function tasks()
    {
        return $this->belongsToMany(Tasks::class, 'task_assign_team', 'team_id', 'task_id');
    }
    public function teamMembers()
        {
            return $this->hasMany(TeamMembers::class, 'team_id');
        }
    // Accessor: fetch team members (id + name)
    public function getMembersInfoAttribute(){
        return $this->teamMembers->map(function ($member) {
            return [
                'id'   => $member->user->id,
                'name' => $member->user->name,
            ];
        })->toArray();
    }
        // Accessor usage example
            // $team = Teams::find(session('team_id')[0]); 
            // $members = $team->members_info; 


       public function getMembersInfoRoleAttribute(){
        return $this->teamMembers->map(function ($member) {
            return [
                'id'   => $member->user->id,
                'name' => $member->user->name,
                'role' => $member->ctrl_status == 1 ? 'TL/PL' : 'Employee',
                'ctrl_status' => $member->ctrl_status,
            ];
        });
    }
      

}

