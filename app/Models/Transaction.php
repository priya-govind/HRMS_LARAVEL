<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $fillable = ['transaction_type','amount','transaction_date','remarks','available_amt','payment_type','bill_refer','is_deleted','last_entry'];

     public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(TransactionAudit::class);
    }
}
