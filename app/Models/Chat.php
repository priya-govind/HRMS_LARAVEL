<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
     use HasFactory;
    protected $table = 'chat';
    protected $fillable = ['sender_id', 'receiver_id', 'message', 'is_read','reply_to','forwarded_from','attachment_path'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function repliedMessage()
    {
        return $this->belongsTo(Chat::class, 'reply_to');
    }
}
