<?php
namespace App\Jobs;

use App\Mail\TaskAssignedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTaskAssignmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user, $task, $assigned, $reporting, $excludeSelf, $sender,$subject,$introLine,$notifyType;

public function __construct($user,$sender, string $subject, string $introLine)
{
    $this->user = $user;
    $this->sender = $sender;
    $this->subject = $subject;
    $this->introLine = $introLine;
}


    public function handle()
    {
        // Send email
        // Mail::to($this->user->email)->send(new TaskAssignedMail(
        //     $this->user,
        //     $this->introLine,
        //     $this->assigned,
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

        $buildTable = function ($heading, $members) use ($userId) {
            if (empty($members)) return '';

            $rows = collect($members)
                ->reject(fn($m) => $this->excludeSelf && $m['user_id'] == $userId)
                ->map(fn($m) =>
                    "<tr><td>{$m['name']}</td><td>{$m['team_name']}</td><td>{$m['role_name']}</td></tr>"
                )
                ->implode('');

            return "<strong>{$heading}</strong><br>
                <table border='1' cellpadding='5' cellspacing='0'>
                    <thead><tr><th>Name</th><th>Team</th><th>Role</th></tr></thead>
                    <tbody>{$rows}</tbody>
                </table><br>";
        };
        if($this->notifyType=='remove_task_notice'){
            $base = "A member is removed from the task: <strong>{$this->task->task_name}</strong><br\><br\>";
        } else {
            $base = "<br\>You have been assigned to the task: <strong>{$this->task->task_name}</strong><br\>Please find the updated details of the members assigned to the task.<br\>";
        }
        
        return $base . $buildTable('Members Assigned', $this->assigned) . $buildTable('Reporting Members', $this->reporting);
    }
}