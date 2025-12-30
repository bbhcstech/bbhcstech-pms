<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClockInNotification extends Notification
{
    use Queueable;

    protected $record;

    public function __construct($record)
    {
        $this->record = $record;
    }

    public function via($notifiable)
    {
        // database only for now (you can add 'broadcast' later for realtime)
        return ['database'];
    }

    /**
     * Ensure any date/time values are serialized to plain strings so the JSON
     * stored in notifications.data is clean and easy to read in blade.
     *
     * This version prefers:
     * 1) $record->clocked_at (if set and DateTime/Carbon)
     * 2) combination of $record->date + $record->clock_in (handles TIME columns)
     * 3) parsing $record->clock_in (if it already contains a datetime string)
     * 4) fallback to now()
     */
    protected function formatClockedAt(): string
    {
        $record = $this->record;

        // 1) explicit clocked_at property (Carbon/DateTime or string)
        $clocked = $record->clocked_at ?? null;
        if ($clocked) {
            if (is_object($clocked) && method_exists($clocked, 'toDateTimeString')) {
                return $clocked->toDateTimeString();
            }
            try {
                return (string) \Carbon\Carbon::parse($clocked)->toDateTimeString();
            } catch (\Throwable $e) {
                // fallthrough to other attempts
            }
        }

        // 2) if both date and clock_in exist, combine them
        $date = $record->date ?? null;
        $time = $record->clock_in ?? null;

        if ($date && $time) {
            try {
                // If $time is a Carbon/DateTime, get H:i:s
                if (is_object($time) && method_exists($time, 'format')) {
                    $timeStr = $time->format('H:i:s');
                } else {
                    $timeStr = trim((string) $time);
                    // add seconds if only H:i provided
                    if (preg_match('/^\d{1,2}:\d{2}$/', $timeStr)) {
                        $timeStr .= ':00';
                    }
                }

                // expect date in Y-m-d; if not, let Carbon handle parsing
                $combined = $date . ' ' . $timeStr;
                return (string) \Carbon\Carbon::parse($combined)->toDateTimeString();
            } catch (\Throwable $e) {
                // fall through to next attempt
            }
        }

        // 3) try parsing clock_in directly (maybe it's already a datetime string)
        if ($time) {
            try {
                return (string) \Carbon\Carbon::parse($time)->toDateTimeString();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 4) fallback to now()
        return now()->toDateTimeString();
    }

    public function toDatabase($notifiable)
    {
        $clockedAt = $this->formatClockedAt();

        // Build a compact payload that the blade can rely on for any notification type.
        return [
            'type'        => 'clock_in', // helpful for blade/icon logic
            'icon'        => 'fa-clock', // frontend can render this if desired
            'record_id'   => $this->record->id ?? null,
            'attendance_id' => $this->record->id ?? null,
            'user_id'     => $this->record->user_id ?? ($notifiable->id ?? null),
            'date'        => $this->record->date ?? null,
            'title'       => 'Clock In',
            'message'     => 'You clocked in at ' . $clockedAt,
            'clocked_at'  => $clockedAt,
            // optional: front-end can build a link using attendance_id/user_id/date
            'action'      => [
                'type' => 'attendance_detail',
                'payload' => [
                    'attendance_id' => $this->record->id ?? null,
                    'user_id'       => $this->record->user_id ?? ($notifiable->id ?? null),
                    'date'          => $this->record->date ?? null
                ]
            ],
        ];
    }

    // keep toArray in case some parts of Laravel call it
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
