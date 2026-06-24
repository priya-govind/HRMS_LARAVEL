<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Teams;

class TeamType extends Model
{
    use SoftDeletes;
    protected $fillable = ['team_typ_name','pm_id','team_color'];
    protected $table = 'team_type';
    

    public function teams() {
        return $this->hasMany(Teams::class, 'team_type');
    }
      public function users()
    {
        return $this->hasMany(User::class, 'team_type');
    }
     public function reportingPerson() {
        return $this->belongsTo(User::class, 'pm_id');
    }


}
