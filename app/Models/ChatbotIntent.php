<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotIntent extends Model
{
     protected $table = 'chatbot_intents';
    protected $fillable = ['name', 'patterns', 'examples','roles_allowed'];
}
