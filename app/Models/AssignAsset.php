<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignAsset extends Model
{
   protected $fillable = ['asset_item_id', 'employee_id', 'assigned_at', 'returned_at'];
   protected $table = 'assign_assets';
    public function assetItems() {
        return $this->belongsTo(AssetItems::class);
    }
    public function employee() {
        return $this->belongsTo(User::class,'employee_id');
    }
}
