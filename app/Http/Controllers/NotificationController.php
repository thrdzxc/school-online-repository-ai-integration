<?php
namespace App\Http\Controllers;

use App\Models\AppNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AppNotification::where('user_id', auth()->id())->latest()->paginate(30);
        return view('notifications', compact('notifications'));
    }

    public function markRead(AppNotification $notification)
    {
        abort_unless((int) $notification->user_id === (int) auth()->id(), 403);
        if (!$notification->read_at) $notification->update(['read_at' => now()]);
        return $notification->url
            ? redirect(\App\Support\WorkspaceUrlGenerator::destination($notification->url, request()->attributes->get('workspace')))
            : back();
    }

    public function markAllRead()
    {
        AppNotification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
