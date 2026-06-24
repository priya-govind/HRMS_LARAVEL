<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $table = 'transaction_items';
    protected $fillable = ['transaction_id','expense_item_id'];


    public function expenseItem()
    {
        return $this->belongsTo(ExpenseItems::class, 'expense_item_id');
    }

}
