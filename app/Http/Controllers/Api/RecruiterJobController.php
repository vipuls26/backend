<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Http\Requests\UpdateJobStatusRequest;
use App\Http\Resources\JobResource;
use App\Models\Job;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RecruiterJobController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $company = $this->companyOrFail($request);

        $jobs = $company->jobs()->with(['company', 'applications', 'savedByUsers'])->latest()->get();

        return $this->success(JobResource::collection($jobs)->resolve($request), 'Recruiter jobs fetched.');
    }

    public function store(StoreJobRequest $request)
    {
        $company = $this->companyOrFail($request);

        $job = $company->jobs()->create($request->validated())->load(['company', 'applications', 'savedByUsers']);

        return $this->success((new JobResource($job))->resolve($request), 'Job created.', 201);
    }

    public function update(UpdateJobRequest $request, Job $job)
    {
        $this->authorizeJob($request, $job);

        $job->update($request->validated());

        return $this->success(
            (new JobResource($job->refresh()->load(['company', 'applications', 'savedByUsers'])))->resolve($request),
            'Job updated.'
        );
    }

    public function updateStatus(UpdateJobStatusRequest $request, Job $job)
    {
        $this->authorizeJob($request, $job);

        $job->update($request->validated());

        return $this->success(
            (new JobResource($job->refresh()->load(['company', 'applications', 'savedByUsers'])))->resolve($request),
            'Job status updated.'
        );
    }

    private function companyOrFail(Request $request)
    {
        $company = $request->user()->company;

        if (! $company) {
            throw ValidationException::withMessages([
                'company' => ['Create a company before managing jobs.'],
            ]);
        }

        return $company;
    }

    private function authorizeJob(Request $request, Job $job): void
    {
        if (! $request->user()->company || $job->company_id !== $request->user()->company->id) {
            abort(403, 'You can only manage jobs belonging to your company.');
        }
    }
}
