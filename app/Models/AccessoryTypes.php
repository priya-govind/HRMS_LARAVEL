<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessoryTypes extends Model
{
     use SoftDeletes;
     protected $table = 'accessory_types';
    protected $fillable = ['accessory_type_name','accessory_type_status'];
}
