<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

final class NotificationController extends Controller
{
    /**
     * Display a list of the user's notifications.
     */
    public function index()
    {
        $user = Auth::user();

        // Fetch notifications with pagination (10 per page)
        $notifications = $user->notifications()->paginate(10);

        return Inertia::render('Notifications', [
            'items' => NotificationResource::collection($notifications),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a specific notification.
     */
    public function delete($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'Notification deleted successfully.');
    }
}
