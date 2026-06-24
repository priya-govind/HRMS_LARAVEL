<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAttributeOptions extends Model
{
    use SoftDeletes;
    protected $table = 'attribute_options';
    protected $fillable = ['attribute_id','attribute_options'];
}
