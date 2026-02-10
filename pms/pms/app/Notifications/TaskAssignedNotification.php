<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $assignedBy;
    protected $notifyTo;

    public function __construct($task, $assignedBy, $notifyTo = 'employee')
    {
        $this->task = $task;
        $this->assignedBy = $assignedBy;
        $this->notifyTo = $notifyTo;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        if ($this->notifyTo === 'employee') {
            return [
                'type' => 'task_assigned',
                'title' => 'New Task Assigned',
                'message' => 'Task "' . $this->task->title . '" assigned to you by ' . $this->assignedBy->name,
                'task_id' => $this->task->id,
                'assigned_by' => $this->assignedBy->name,
                'assigned_by_id' => $this->assignedBy->id,
                'url' => url('/employee/tasks/' . $this->task->id),
                'icon' => 'fa-tasks',
                'color' => 'primary',
                'for' => 'employee',
            ];
        } else {
            return [
                'type' => 'task_assigned_admin',
                'title' => 'Task Assignment Done',
                'message' => 'You assigned task "' . $this->task->title . '" to ' . ($this->task->assignedTo->name ?? 'Employee'),
                'task_id' => $this->task->id,
                'assigned_to' => $this->task->assignedTo->name ?? 'Employee',
                'assigned_to_id' => $this->task->assigned_to,
                'url' => url('/admin/tasks/' . $this->task->id),
                'icon' => 'fa-tasks',
                'color' => 'info',
                'for' => 'admin',
            ];
        }
    }
}
