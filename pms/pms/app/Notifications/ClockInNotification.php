<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClockInNotification extends Notification
{
    use Queueable;

    protected $record;
    protected $notifyTo; // 'admin' or 'both'

    public function __construct($record, $notifyTo = 'admin')
    {
        $this->record = $record;
        $this->notifyTo = $notifyTo;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    protected function formatClockedAt(): string
    {
        // Your existing code...
        return now()->toDateTimeString();
    }

    public function toDatabase($notifiable)
    {
        $clockedAt = $this->formatClockedAt();

        $data = [
            'type' => 'clock_in',
            'title' => 'Clock In Recorded',
            'message' => $this->notifyTo === 'admin'
                ? 'Employee ' . ($this->record->user->name ?? 'Someone') . ' clocked in at ' . $clockedAt
                : 'You clocked in at ' . $clockedAt,
            'record_id' => $this->record->id,
            'user_id' => $this->record->user_id,
            'clocked_at' => $clockedAt,
            'url' => $this->notifyTo === 'admin'
                ? url('/admin/attendance')
                : url('/employee/attendance'),
            'icon' => 'fa-clock',
            'color' => 'success',
            'for' => $this->notifyTo,
            'action_by' => auth()->user()->name ?? 'System',
            'action_by_id' => auth()->id(),
        ];

        return $data;
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
