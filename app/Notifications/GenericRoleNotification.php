<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class GenericRoleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;
    public $subject;
    public $title;
    public $assigned;
    public $reporting;
    public $excludeSelf;

    public function __construct($user, $subject, $title, $assigned, $reporting, $excludeSelf = false)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->title = $title;
        $this->assigned = $assigned;
        $this->reporting = $reporting;
        $this->excludeSelf = $excludeSelf;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toMail($notifiable)
    {
     return (new \Illuminate\Notifications\Messages\MailMessage)
        ->subject($this->subject)
        ->view('emails.task_notification', [
            'user' => $this->user,
            'title' => $this->title,
            'assigned' => $this->assigned,
            'reporting' => $this->reporting,
            'excludeSelf' => $this->excludeSelf,
        ]);

    }

    private function generateTable($heading, $data, $excludeSelf)
    {
        $rows = '';
        foreach ($data as $member) {
            if ($excludeSelf && $member['user_id'] == $this->user->id) continue;
            $rows .= "<tr><td>{$member['name']}</td><td>{$member['team_name']}</td><td>{$member['role_name']}</td></tr>";
        }

        return "<strong>{$heading}</strong><br>
                <table border='1' cellpadding='5' cellspacing='0'>
                    <thead><tr><th>Name</th><th>Team</th><th>Role</th></tr></thead>
                    <tbody>{$rows}</tbody>
                </table><br>";
    }
}