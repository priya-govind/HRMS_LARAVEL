<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Policies\BotmenuPolicy;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;


class BotMenu extends Model
{
   // use HasFactory;
   // use SoftDeletes;
    protected $table = 'bot_menus';
    protected $fillable = ['bot_name','parent_id','command','is_active','order_by','support_access','service_name','service_method','required_fields',];
    public $timestamps = false;
    protected $casts = [  'required_fields' => 'array',];

   // protected $guarded = [];

    public static function SecureBotUser($bot_id,$permission_id){
        $user = Auth::user(); 
      $policy = app(BotmenuPolicy::class);
        //Log::info('permission_id =>'.$permission_id);
            $result1 = $policy->BotmenuPermit($bot_id,$permission_id);
            if(!$result1){
                $full_routeName = Route::currentRouteName();
                activity()
                ->causedBy($user) // Log the authenticated user
                ->withProperties([
                    'request_url' => $full_routeName,
                    'status' => 'Unauthorized Access Attempt'
                ])
                ->log('User tried to access an unauthorized page.'.$bot_id);
    
                return false;
            } else {
                return true;
            }
    }
    public function RoleBotPermissionss()
    {
        return $this->hasMany(RoleBotPermissions::class, 'roles_id');
    }
    public function bot_children(){
        return $this->hasMany(BotMenu::class,'parent_id');
    }

    public function bot_parent()
{
    return $this->belongsTo(BotMenu::class, 'parent_id');
}
public function bot_permissions()
{
    return $this->belongsToMany(Permission::class, 'role_bot_menus_permissions', 'bot_id', 'permission_id')
                ->withPivot('roles_id');
}
public function rolePermissions()
{
    return $this->hasMany(RoleBotPermissions::class, 'bot_id');
}

}
