<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAttribute extends Model
{
    use SoftDeletes;
    protected $table = 'attribute';
    protected $fillable = ['attribute_name','attribute_status'];

     public function options()
    {
        return $this->hasMany(AssetAttributeOptions::class, 'attribute_id');
    }

}
