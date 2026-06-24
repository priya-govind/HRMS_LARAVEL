<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timesheet_bkup extends Model
{
    protected $fillable = ['emp_id','create_dt','day','from_time','to_time','project_id','module','description',];
    protected $table = 'timesheet';
    public function Projects(){
     return $this->belongsTo(Projects::class, 'project_id', 'id');
}
}
