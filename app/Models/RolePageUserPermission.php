<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePageUserPermission extends Model
{
    protected $table = 'role_page_user_permission';
    protected $fillable = ['role_id','user_id', 'page_id', 'permission_id'];

    /**Used in side bar listing menu */
    public function user_page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
    
}
