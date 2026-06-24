<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = ['asset_name', 'asset_type','asset_brand', 'serial_number', 'asset_status'];
     protected $table = 'inventories';
    
    public function assignments() {
        return $this->hasMany(InventoryAssignment::class,'inventory_id');
    }
    public function AssetType(){
        return $this->hasOne(ItemType::class, 'id', 'asset_type');
    }
     public function AssetBrand(){
        return $this->hasOne(Brands::class, 'id', 'asset_brand');
    }

}
