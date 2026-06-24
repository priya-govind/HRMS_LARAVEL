<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseItems extends Model
{
     protected $table = 'expense_items';
    protected $fillable = ['expense_type_name','expense_type_status'];
}
