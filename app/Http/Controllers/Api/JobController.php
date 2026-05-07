<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Models\Job;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $jobs = Job::query()
            ->with(['company', 'applications', 'savedByUsers'])
            ->where('status', 'published')
            ->latest()
            ->get();

        return $this->success(JobResource::collection($jobs)->resolve($request), 'Jobs fetched.');
    }
}
