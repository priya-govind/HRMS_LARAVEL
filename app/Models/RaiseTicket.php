<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TicketType;
use App\Models\ProblemType;

class RaiseTicket extends Model
{
    protected $fillable = ['ticket_type_id','problem_type_id','ticket_name','ticket_desc','ticket_raised_by','ticket_status','ticket_solved_by'];
    protected $table = 'raise_ticket';

    public function ticketType(){
        return $this->belongsTo(TicketType::class, 'ticket_type_id','id');
    }
     public function problemType(){
        return $this->belongsTo(ProblemType::class, 'problem_type_id','id');
    }
     public function ticketStatus(){
        return $this->belongsTo(ProjectStatus::class, 'ticket_status','id');
    }
    public function TicketOwner(){
       return $this->belongsTo(User::class, 'ticket_raised_by','id');  
    }
    public function AssignedTicketMembers(){
        return $this->hasMany(TicketAssignMembers::class, 'ticket_id', 'id');
    }
   

}
