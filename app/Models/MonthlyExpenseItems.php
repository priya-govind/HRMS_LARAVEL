<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyExpenseItems extends Model
{
   protected $table = 'monthly_expense_items';
   protected $fillable = ['expense_id','expense_item_id','exp_amount'];
    public $timestamps = false;


     public function expenseItem()
    {
        return $this->belongsTo(ExpenseItems::class, 'expense_item_id');
    }

}
