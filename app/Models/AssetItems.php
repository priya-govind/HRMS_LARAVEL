<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AssetItems extends Model
{
   
    protected $table = 'asset_items';
    protected $fillable = ['item_name','item_type','item_category','item_brand','serial_number','status','purchased_amount','purchased_date','expiry_date'];
    
    // If you also need brand relation
    public function itemBrand()
    {
        return $this->belongsTo(Brands::class, 'item_brand', 'id');
    }

    public function getItemCategoryNameAttribute()
    {
        if (!$this->item_category || !$this->item_type) {
            return '';
        }

        switch ($this->item_type) {
            case 'asset':
                $model = \App\Models\AssetTypes::find($this->item_category);
                return $model?->asset_type_name ?? '';

            case 'accessory':
                $model = \App\Models\AccessoryTypes::find($this->item_category);
                return $model?->accessory_type_name ?? '';

            case 'components':
                $model = \App\Models\ComponentTypes::find($this->item_category);
                return $model?->component_type_name ?? '';

            case 'licenses':
                $model = \App\Models\SoftwareLicenses::find($this->item_category);
                return $model?->license_type_name ?? '';

            default:
                return '';
        }
    }
     public function assignments() {
        return $this->hasMany(AssignAsset::class,'asset_item_id');
    }
    public function ItemConfigurationValues(){
        return $this->hasMany(ItemAttributeRelation::class,'item_id','id') ->with(['attribute', 'option']);
    }


}
