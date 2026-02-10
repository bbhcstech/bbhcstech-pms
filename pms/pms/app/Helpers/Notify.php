<?php

namespace App\Helpers;

use App\Models\User;
use App\Notifications\ClockInNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TicketCreatedNotification;

class Notify
{
    /**
     * Notify when EMPLOYEE clocks in (notify all admins)
     */
    public static function employeeClockedIn($attendance)
    {
        $employee = $attendance->user;
        $admins = User::where('is_admin', 1)->get();

        foreach ($admins as $admin) {
            $admin->notify(new ClockInNotification($attendance, 'admin'));
        }

        // Also notify employee
        $employee->notify(new ClockInNotification($attendance, 'employee'));

        return true;
    }

    /**
     * Notify when ADMIN assigns task (notify employee)
     */
    public static function taskAssigned($task, $admin, $employeeId)
    {
        $employee = User::find($employeeId);
        if (!$employee) return false;

        // Notify employee
        $employee->notify(new TaskAssignedNotification($task, $admin, 'employee'));

        // Also notify admin (optional confirmation)
        $admin->notify(new TaskAssignedNotification($task, $admin, 'admin'));

        return true;
    }

    /**
     * Notify when EMPLOYEE creates ticket (notify all admins)
     */
    public static function ticketCreated($ticket, $employee)
    {
        $admins = User::where('is_admin', 1)->get();

        foreach ($admins as $admin) {
            $admin->notify(new TicketCreatedNotification($ticket, $employee, 'admin'));
        }

        // If ticket is assigned to someone, notify them too
        if ($ticket->assigned_to) {
            $assignedUser = User::find($ticket->assigned_to);
            if ($assignedUser) {
                $assignedUser->notify(new TicketCreatedNotification($ticket, $employee, 'employee'));
            }
        }

        return true;
    }

    /**
     * Notify when ADMIN updates employee info
     */
    public static function employeeUpdated($employee, $admin, $changes = [])
    {
        $notificationData = [
            'type' => 'employee_updated',
            'title' => 'Profile Updated',
            'message' => 'Your profile has been updated by admin',
            'changes' => $changes,
            'updated_by' => $admin->name,
            'url' => url('/employee/profile'),
            'icon' => 'fa-user-edit',
            'color' => 'warning',
        ];

        $employee->notify(new \App\Notifications\ActionNotification($notificationData));

        return true;
    }

    /**
     * Create a custom notification
     */
    public static function custom($user, $data)
    {
        $defaults = [
            'type' => 'custom',
            'title' => 'Notification',
            'message' => '',
            'url' => '#',
            'icon' => 'fa-bell',
            'color' => 'primary',
        ];

        $user->notify(new \App\Notifications\ActionNotification(array_merge($defaults, $data)));

        return true;
    }

    /**
     * Notify multiple users
     */
    public static function broadcast($users, $data)
    {
        foreach ($users as $user) {
            self::custom($user, $data);
        }

        return true;
    }
}
