<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\NotifyAdminToEmployees;
use App\Notifications\NotifyEmployeeToAdmins;

class NotificationController extends Controller
{
    /**
     * Display all notifications
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // 🔥 CHANGE THIS: Use ONE view file instead of two
        return view('notifications.index', compact('notifications'));
    }


    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['status' => 'ok']);
    }

    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        auth()->user()->notifications()->delete();

        return back()->with('success', 'All notifications cleared');
    }

    /**
     * Send notification from Admin to ALL Employees
     */
    public function adminToEmployees(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'url' => 'nullable|url',
        ]);

        $employees = User::where('is_admin', false)->get();

        $data = [
            'title' => $request->title,
            'message' => $request->message,
            'url' => $request->url,
            'ticket_id' => $request->ticket_id,
        ];

        foreach ($employees as $employee) {
            $employee->notify(new NotifyAdminToEmployees($data));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent to all employees',
            'count' => $employees->count()
        ]);
    }

    /**
     * Send notification from Employee to ALL Admins
     */
    public function employeeToAdmins(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'url' => 'nullable|url',
        ]);

        $admins = User::where('is_admin', true)->get();

        $data = [
            'title' => $request->title,
            'message' => $request->message,
            'url' => $request->url,
            'ticket_id' => $request->ticket_id,
        ];

        foreach ($admins as $admin) {
            $admin->notify(new NotifyEmployeeToAdmins($data));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent to all admins',
            'count' => $admins->count()
        ]);
    }

    /**
     * Send notification to specific users
     */
    public function sendToUsers(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $sender = auth()->user();

        foreach ($users as $user) {
            $notificationClass = $sender->is_admin
                ? NotifyAdminToEmployees::class
                : NotifyEmployeeToAdmins::class;

            $user->notify(new $notificationClass([
                'title' => $request->title,
                'message' => $request->message,
                'url' => $request->url,
                'ticket_id' => $request->ticket_id,
            ]));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent to selected users',
            'count' => $users->count()
        ]);
    }

    /**
     * Delete single notification
     */
    public function delete($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->delete();
        }

        return response()->json(['status' => 'ok']);
    }
}
