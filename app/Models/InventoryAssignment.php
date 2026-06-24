<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAssignment extends Model
{
   protected $fillable = ['inventory_id', 'employee_id', 'assigned_at', 'returned_at'];
   protected $table = 'inventory_assignments';

    public function inventory() {
        return $this->belongsTo(Inventory::class);
    }

    public function employee() {
        return $this->belongsTo(User::class,'employee_id');
    }

}
