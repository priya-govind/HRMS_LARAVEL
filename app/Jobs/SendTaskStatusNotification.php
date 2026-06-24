<?php
namespace App\Jobs;

use App\Mail\TaskAssignedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TaskStatusTeam;

class SendTaskStatusNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user, $task,  $reporting, $excludeSelf, $sender,$subject,$introLine,$notifyType,$teamName,$status,$team_ids;

public function __construct($user, $task, $reporting, $excludeSelf, $sender, string $subject, string $introLine, string $notifyType,string $teamName,string $status,array $team_ids)
{
    $this->user = $user;
    $this->task = $task;
    $this->reporting = $reporting;
    $this->excludeSelf = $excludeSelf;
    $this->sender = $sender;
    $this->subject = $subject;
    $this->introLine = $introLine;
    $this->notifyType = $notifyType;
    $this->teamName = $teamName;
    $this->team_ids=$team_ids;
}


    public function handle()
    {
        // Send email
        // Mail::to($this->user->email)->send(new TaskAssignedMail(
        //     $this->user,
        //     $this->introLine,
        //     $this->reporting,
        //     $this->excludeSelf
        // ));

        // Format tables
        $message = $this->buildHtmlNotificationMessage();

        // Insert into notification table
        $this->insertNotification($message);
    }
private function insertNotification(string $message)
{
  logger()->debug('Inserting task assignment notification', [
    'receiver_id' => $this->user->id,
    'sender_id' => $this->sender['id'],
    'notify_type' => $this->notifyType,
]);

    DB::table('notification')->insert([
        'notify_type'   => $this->notifyType,
        'receiver_id'   => $this->user->id,
        'sender_id'     => $this->sender['id'],
       'sender_name' => $this->sender['name'] . ' - ' . ($this->sender['role'] ?? 'User'),
        'receiver_name' => $this->user->name,
        'is_read'       => config('global.notify_unread'),
        'subject'       => $this->subject,
        'message'       => $message,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
}
    private function buildHtmlNotificationMessage()
    {
        $userId = $this->user->id;
        $team_ids=$this->team_ids;
        $teamName=$this->teamName;
         $comments = TaskStatusTeam::whereIn('team_id', $team_ids)
                    ->pluck('comments')
                    ->first();
                    $base = "";
        if($this->status==config('global.approval_waiting_status')){
            $base = "<p>Following Team has completed the task and waiting for your approval as completed.the details are as follows:</p>";
             $base .= "<p>Task Name: <strong>{$this->task->task_name}</strong></p>
        <p>Team Name: <strong>{$teamName}</strong></p>
        <p>Comments:<strong>{$comments}</strong></p><p>Please visit Fortgrid site for more Information.</p>";
        } 
        if($this->status==config('global.reopen_status')){
        $base = "<p>Following Task was reopened by ".session('user_name')."-".session('role_name').".the details are as follows:</p>";
         $base .= "<p>Task Name: <strong>{$this->task->task_name}</strong></p>
        <p>Team Name: <strong>{$teamName}</strong></p>
        <p>Comments:<strong>{$comments}</strong></p><p>Please visit Fortgrid site for more Information.</p>";
        }
        Log::info('Mail content being built:', ['content' => $base]);

        
        return $base;
    }
}