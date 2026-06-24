<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProblemType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['ticket_type_id','problem_type','problem_type_active'];
    protected $table = 'problem_types';
    
}
