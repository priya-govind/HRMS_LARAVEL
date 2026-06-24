<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
     protected $table = 'item_types';
    protected $fillable = ['item_type_name','item_status'];
}
