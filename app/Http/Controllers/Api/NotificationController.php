<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $notifications = $request->user()
            ->portalNotifications()
            ->latest()
            ->get();

        return $this->success(NotificationResource::collection($notifications)->resolve($request), 'Notifications fetched.');
    }

    public function markRead(Request $request)
    {
        $request->user()
            ->portalNotifications()
            ->delete();

        return $this->success(null, 'Notifications cleared.');
    }
}
