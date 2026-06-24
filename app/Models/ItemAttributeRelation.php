<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemAttributeRelation extends Model
{
    protected $table = 'item_attribute_relation';
    protected $fillable = ['item_id','attribute_id','option_id'];

     public function attribute()
    {
        return $this->belongsTo(AssetAttribute::class, 'attribute_id', 'id');
    }

    public function option()
    {
        return $this->belongsTo(AssetAttributeOptions::class, 'option_id', 'id');
    }

}
