<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetTypes extends Model
{
    use SoftDeletes;
     protected $table = 'asset_types';
    protected $fillable = ['asset_type_name','asset_type_status'];

     public function configurations()
    {
        return $this->hasMany(AssetConfiguration::class, 'asset_id');
    }

    // Accessor to get attributes with values
    public function getAttributesWithValuesAttribute()
    {
        return $this->configurations->map(function ($config) {
            return [
                'attribute_name' => $config->attribute->attribute_name,
                'attribute_values' => $config->attribute->options->pluck('attribute_options')
                                                                ->toArray()
            ];
        });
    }

}
