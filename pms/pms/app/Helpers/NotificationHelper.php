<?php

namespace App\Helpers;

use App\Models\User;
use App\Notifications\NotifyAdminToEmployees;
use App\Notifications\NotifyEmployeeToAdmins;

class NotificationHelper
{
    /**
     * When employee does something → notify all admins
     */
    public static function notifyAdmins($title, $message, $url = null, $ticket_id = null)
    {
        // Get all admins
        $admins = User::where('is_admin', true)->get();

        // Get current employee
        $employee = auth()->user();

        // Prepare data
        $data = [
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'ticket_id' => $ticket_id,
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'type' => 'employee_to_admin',
            'time' => now()->format('Y-m-d H:i:s'),
        ];

        // Send to each admin
        foreach ($admins as $admin) {
            $admin->notify(new NotifyEmployeeToAdmins($data));
        }

        return true;
    }

    /**
     * When admin does something → notify all employees
     */
    public static function notifyEmployees($title, $message, $url = null, $ticket_id = null)
    {
        // Get all employees
        $employees = User::where('is_admin', false)->get();

        // Get current admin
        $admin = auth()->user();

        // Prepare data
        $data = [
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'ticket_id' => $ticket_id,
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'type' => 'admin_to_employee',
            'time' => now()->format('Y-m-d H:i:s'),
        ];

        // Send to each employee
        foreach ($employees as $employee) {
            $employee->notify(new NotifyAdminToEmployees($data));
        }

        return true;
    }

    /**
     * When admin does something → notify specific employee
     */
    public static function notifySingleEmployee($employee_id, $title, $message, $url = null)
    {
        $employee = User::find($employee_id);

        if (!$employee) {
            return false;
        }

        $admin = auth()->user();

        $data = [
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'type' => 'admin_to_employee',
            'time' => now()->format('Y-m-d H:i:s'),
        ];

        $employee->notify(new NotifyAdminToEmployees($data));

        return true;
    }

    /**
     * When employee does something → notify specific admin
     */
    public static function notifySingleAdmin($admin_id, $title, $message, $url = null)
    {
        $admin = User::find($admin_id);

        if (!$admin) {
            return false;
        }

        $employee = auth()->user();

        $data = [
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'type' => 'employee_to_admin',
            'time' => now()->format('Y-m-d H:i:s'),
        ];

        $admin->notify(new NotifyEmployeeToAdmins($data));

        return true;
    }
}
