<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionAudit extends Model
{
    protected $fillable = ['transaction_id', 'action', 'original_data', 'edited_by'];

    protected $casts = [
        'original_data' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
