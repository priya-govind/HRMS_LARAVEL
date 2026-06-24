<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotResponse extends Model
{
    protected $table = 'chatbot_responses';
    protected $fillable = ['chatbot_intent_id', 'template', 'variables'];
}
