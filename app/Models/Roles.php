<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $fillable = ['role_name'];

    public function users()
    {
        return $this->belongsToMany(User::class,'roles_user','roles_id','user_id');
    }
    public function roleCategoryPermissions()
    {
        return $this->hasMany(RoleCategoryPermission::class, 'roles_id');
    }
    public function categoryPermissions()
{
    return $this->hasMany(RoleCategoryPermission::class, 'role_id');
}
   public function roleBotmenuPermissions()
    {
        return $this->hasMany(RoleBotPermissions::class, 'roles_id');
    }

}
