<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\PortalNotification;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CandidateDashboardController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request)
    {
        $user = $request->user();

        return $this->success([
            'published_jobs_count' => Job::where('status', 'published')->count(),
            'applications_count' => $user->applications()->count(),
            'saved_jobs_count' => $user->savedJobs()->count(),
            'unread_notifications_count' => PortalNotification::where('user_id', $user->id)->whereNull('read_at')->count(),
        ], 'Candidate dashboard fetched.');
    }
}
