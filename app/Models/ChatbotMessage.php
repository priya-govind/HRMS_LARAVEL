<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
   protected $table = 'chatbot_messages';
   protected $fillable = ['chatbot_session_id', 'sender', 'content','extras','request_payload','response_payload'];
    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];
}
