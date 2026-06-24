<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComponentTypes extends Model
{
    use SoftDeletes;
    protected $table = 'component_types';
    protected $fillable = ['component_type_name','component_type_status'];
}
