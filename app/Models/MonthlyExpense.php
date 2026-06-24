<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyExpense extends Model
{
    protected $table = 'monthly_expense';
    protected $fillable = ['transaction_type','trans_amount','transaction_date','remarks','available_amt','payment_type','bill_refer','is_deleted','last_entry'];

     public function items()
    {
        return $this->hasMany(MonthlyExpenseItems::class,'expense_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(TransactionAudit::class);
    }
}
