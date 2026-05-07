<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\PortalNotification;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class RecruiterApplicationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $company = $request->user()->company;

        if (! $company) {
            return $this->success([], 'Applications fetched.');
        }

        $applications = Application::query()
            ->with(['job.company', 'user'])
            ->whereHas('job', fn ($query) => $query->where('company_id', $company->id))
            ->latest()
            ->get();

        return $this->success(ApplicationResource::collection($applications)->resolve($request), 'Applications fetched.');
    }

    public function updateStatus(UpdateApplicationStatusRequest $request, Application $application)
    {
        $application->load(['job.company', 'user']);
        $company = $request->user()->company;

        if (! $company || $application->job->company_id !== $company->id) {
            abort(403, 'You can only review applications for your company jobs.');
        }

        $validated = $request->validated();

        $application->update(['status' => $validated['status']]);

        if (in_array($validated['status'], ['Interview Scheduled', 'Accepted', 'Rejected'], true)) {
            PortalNotification::create([
                'user_id' => $application->user_id,
                'title' => $this->notificationTitle($validated['status']),
                'message' => "Your application for {$application->job->title} at {$application->job->company->name} was {$this->notificationMessageStatus($validated['status'])}.",
            ]);
        }

        return $this->success(
            (new ApplicationResource($application->refresh()->load(['job.company', 'user'])))->resolve($request),
            'Application status updated.'
        );
    }

    private function notificationTitle(string $status): string
    {
        return match ($status) {
            'Accepted' => 'Application accepted',
            'Rejected' => 'Application rejected',
            'Interview Scheduled' => 'Interview scheduled',
        };
    }

    private function notificationMessageStatus(string $status): string
    {
        return match ($status) {
            'Accepted' => 'accepted',
            'Rejected' => 'rejected',
            'Interview Scheduled' => 'moved to interview scheduled',
        };
    }
}
