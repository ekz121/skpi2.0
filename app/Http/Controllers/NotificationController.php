<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function destroy(Request $request, AppNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->delete();

        return back()->with('success', 'Notifikasi dihapus.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        AppNotification::where('user_id', $request->user()->id)->delete();

        return back()->with('success', 'Semua notifikasi dihapus.');
    }
}
