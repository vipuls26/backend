<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\Job;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $applications = $request->user()
            ->applications()
            ->with(['job.company', 'user'])
            ->latest()
            ->get();

        return $this->success(ApplicationResource::collection($applications)->resolve($request), 'Applications fetched.');
    }

    public function store(StoreApplicationRequest $request, Job $job)
    {
        if ($job->status !== 'published') {
            abort(403, 'You can only apply to published jobs.');
        }

        if (Application::where('job_id', $job->id)->where('user_id', $request->user()->id)->exists()) {
            throw ValidationException::withMessages([
                'job' => ['You have already applied to this job.'],
            ]);
        }

        $application = Application::create([
            ...$request->validated(),
            'job_id' => $job->id,
            'user_id' => $request->user()->id,
            'status' => 'Pending',
        ])->load(['job.company', 'user']);

        return $this->success((new ApplicationResource($application))->resolve($request), 'Application submitted.', 201);
    }
}
