<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifyType extends Model
{
   protected $table = 'notify_type';
   protected $fillable = ['notify_type','notify_icon'];
   public $timestamps = false;
}
