<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = UserNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, int $notification)
    {
        $model = UserNotification::where('user_id', $request->user()->id)->findOrFail($notification);
        $model->update(['read_at' => now()]);

        return back();
    }
}
