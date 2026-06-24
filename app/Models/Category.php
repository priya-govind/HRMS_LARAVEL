<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Policies\CategoryPolicy;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;


class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'category';
    protected $fillable = ['category_name','url_link','parent_id','is_active_cat','support_access'];
    public $timestamps = false;
   // protected $guarded = [];

    public static function SecureUser($category_id,$permission_id){
        $user = Auth::user(); 
      $policy = app(CategoryPolicy::class);
        //Log::info('permission_id =>'.$permission_id);
            $result1 = $policy->CategoryPermit($category_id,$permission_id);
            if(!$result1){
                $full_routeName = Route::currentRouteName();
                activity()
                ->causedBy($user) // Log the authenticated user
                ->withProperties([
                    'request_url' => $full_routeName,
                    'status' => 'Unauthorized Access Attempt'
                ])
                ->log('User tried to access an unauthorized page.'.$category_id);
    
                return false;
            } else {
                return true;
            }
    }
    public function roleCategoryPermissions()
    {
        return $this->hasMany(RoleCategoryPermission::class, 'roles_id');
    }
    public function children(){
        return $this->hasMany(Category::class,'parent_id');
    }

    public function parent()
{
    return $this->belongsTo(Category::class, 'parent_id');
}
public function permissions()
{
    return $this->belongsToMany(Permission::class, 'role_category_permissions', 'category_id', 'permission_id')
                ->withPivot('roles_id');
}
public function rolePermissions()
{
    return $this->hasMany(RoleCategoryPermission::class, 'category_id');
}

}
