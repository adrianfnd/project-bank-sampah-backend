<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function getCostomerNotifications()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }

            $customerCostumers = User::whereHas('role', function ($query) {
                $query->where('name', 'costumer');
            })->pluck('id');

            $notifications = Notification::whereIn('user_id', $customerCostumers)
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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getStaffNotifications()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 401);
            }

            $staffUsers = User::whereHas('role', function ($query) {
                $query->where('name', 'staff');
            })->pluck('id');

            $notifications = Notification::whereIn('user_id', $staffUsers)
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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

