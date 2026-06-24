<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillType extends Model
{
    protected $table = 'bill_type';
    protected $fillable = ['bill_typ_name','bill_typ_status'];
}
