<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAssignMembers extends Model
{
    protected $fillable = ['ticket_id','owner_id','assign_mem_id','assign_comments','reply_to'];
    protected $table = 'ticket_assign_members';
    public function user(){
        return $this->belongsTo(User::class, 'assign_mem_id','id');
    }
    
}
