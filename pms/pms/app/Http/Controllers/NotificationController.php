<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        // prefer searching unread first (safer) then all notifications
        $notification = $user->unreadNotifications()->where('id', $id)->first()
                        ?? $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            // respond appropriately for AJAX or normal requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'not_found'], 404);
            }
            return back()->withErrors('Notification not found');
        }

        $notification->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('success', 'Notification marked as read');
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('success', 'All notifications marked as read');
    }
    public function clearAll()
{
    auth()->user()->notifications()->delete();

    return back()->with('success', 'All notifications cleared');
}

    
    
}
