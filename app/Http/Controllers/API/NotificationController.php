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
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'user_id' => $notification->user_id,
                    'description' => $notification->description,
                    'type' => $notification->type,
                    'status' => $notification->status,
                    'date' => $notification->created_at->toDateString(),
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                ];
            });

        return response()->json([
            'data' => $notifications,
        ], 200);
    }

    public function getStaffNotifications()
    {
        $notifications = Notification::where('user_id', null)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'user_id' => $notification->user_id,
                    'description' => $notification->description,
                    'type' => $notification->type,
                    'status' => $notification->status,
                    'date' => $notification->created_at->toDateString(),
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                ];
            });

        return response()->json([
            'data' => $notifications,
        ], 200);
    }
}
