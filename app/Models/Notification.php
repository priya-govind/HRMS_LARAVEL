<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';
    protected $fillable = ['sender_id','receiver_id','sender_name','receiver_name','subject','message','notify_type','is_read'];
}
