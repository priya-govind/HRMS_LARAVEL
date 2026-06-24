<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectStatus extends Model
{
    use SoftDeletes;
    protected $fillable = ['proj_status_name','task_set_status','emp_set_status','proj_set_status','ticket_set_status'];
    protected $table = 'project_status';
}
