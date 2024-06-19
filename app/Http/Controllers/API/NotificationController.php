<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getUserNotifications()
    {
        $user = Auth::user();

        $notifications = Notification::where('user_id', $user->id)
            ->orWhere('user_id', null)
            ->get();

        return response()->json([
            'data' => $notifications,
        ], 200);
    }

    public function getStaffNotifications()
    {
        $notifications = Notification::where('user_id', null)->get();

        return response()->json([
            'data' => $notifications,
        ], 200);
    }
}