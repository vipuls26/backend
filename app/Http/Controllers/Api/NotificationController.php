<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\PortalNotification;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->portalNotifications()
            ->latest()
            ->get();

        return $this->success(NotificationResource::collection($notifications)->resolve($request), 'Notifications fetched.');
    }

    public function markRead(Request $request): JsonResponse
    {
        $request->user()
            ->portalNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(null, 'Notifications marked as read.');
    }

    public function markOneRead(Request $request, PortalNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(404);
        }

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return $this->success(
            (new NotificationResource($notification->refresh()))->resolve($request),
            'Notification marked as read.'
        );
    }

    public function destroy(Request $request, PortalNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(404);
        }

        $notification->delete();

        return $this->success(null, 'Notification deleted.');
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $request->user()
            ->portalNotifications()
            ->delete();

        return $this->success(null, 'Notifications deleted.');
    }
}
