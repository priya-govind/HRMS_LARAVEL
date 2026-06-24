<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleCategoryPermission extends Model
{
    use HasFactory;

    protected $table = 'role_category_permissions';
    protected $fillable = ['roles_id', 'category_id', 'permission_id'];
    public $timestamps = false;


    // Define the relationships
/*    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }*/

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    
}

