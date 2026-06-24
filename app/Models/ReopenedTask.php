<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReopenedTask extends Model
{
    protected $table = 'reopened_tasks'; 
    protected $fillable = ['task_id','emp_id','team_id','reopen_type','ctrl_status','task_status']; 

}
