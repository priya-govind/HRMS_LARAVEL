<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class AssetConfiguration extends Model
{
  //  use SoftDeletes;
    protected $table = 'asset_configuration';
    protected $fillable = ['asset_id','attribute_id'];
     public function attribute()
    {
        return $this->belongsTo(AssetAttribute::class, 'attribute_id');
    }

}
