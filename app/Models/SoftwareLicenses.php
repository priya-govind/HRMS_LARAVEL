<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SoftwareLicenses extends Model
{
    use SoftDeletes;
    protected $table = 'licenses_types';
    protected $fillable = ['license_type_name','license_type_status'];
}
