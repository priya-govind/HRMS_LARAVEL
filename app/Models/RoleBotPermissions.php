<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleBotPermissions extends Model
{
   // use HasFactory;

    protected $table = 'role_bot_menus_permissions';
    protected $fillable = ['roles_id', 'bot_id', 'permission_id'];
    public $timestamps = false;
     public function bot_menu()
    {
        return $this->belongsTo(BotMenu::class, 'bot_id');
    }
}
