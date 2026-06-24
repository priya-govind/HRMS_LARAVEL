<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskFileUploads extends Model
{
    protected $fillable = ['task_id','uploaded_by','original_name','stored_name','mime_type','size'];
    protected $table = 'pm_task_uploads';
}
