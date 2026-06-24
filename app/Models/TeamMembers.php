<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMembers extends Model
{
    protected $fillable = ['team_id','emp_id','proj_type','ctrl_status'];
    protected $table = 'team_members';
    
    public function user()
    {
        return $this->belongsTo(User::class, 'emp_id', 'id');
    }
    // public function team(){
    //       return $this->belongsTo(Teams::class, 'team_id', 'id') ->select('teams.id', 'teams.team_name');
    // }
    public function team(){
        return $this->belongsTo(Teams::class, 'team_id')->select(['id', 'team_name']);
    }
    public function projType(){
        return $this->belongsTo(ProjectType::class, 'proj_type');
    }
     // Accessor: fetch member’s basic info
    public function getBasicInfoAttribute()
    {
        return [
            'id'   => $this->user->id,
            'name' => $this->user->name,
        ];
    }
    // Accessor usage example
    // $members = TeamMembers::whereIn('team_id', session('team_id'))->get();
    // foreach ($members as $member) {
    //     echo json_encode($member->basic_info);
    //     // {"id":1,"name":"Priya"}
    // }
    // Accessor: fetch member’s basic info with role
     public function getBasicInfoRoleAttribute()
    {
        return [
            'id'   => $this->user->id,
            'name' => $this->user->name,
            'role' => $this->ctrl_status == 1 ? 'TL/PL' : 'Employee',
        ];
    }

}

