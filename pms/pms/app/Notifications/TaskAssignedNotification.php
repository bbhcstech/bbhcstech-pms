<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    protected $task;

    public function __construct($task)
    {
        $this->task = $task;
    }

    // Channels
    public function via($notifiable)
    {
        return ['database']; // ✅ Will save in notifications table
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'title'   => 'New Task Assigned',
            'message' => 'You have been assigned to task: ' . $this->task->title,
        ];
    }
}

