<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotSession extends Model
{
    protected $table = 'chatbot_sessions';
    protected $fillable = ['user_id', 'status', 'metadata'];
}
