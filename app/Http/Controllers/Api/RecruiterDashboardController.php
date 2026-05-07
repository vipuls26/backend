<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class RecruiterDashboardController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request)
    {
        $company = $request->user()->company;

        return $this->success([
            'has_company' => $company !== null,
            'jobs_count' => $company ? $company->jobs()->count() : 0,
            'published_jobs_count' => $company ? $company->jobs()->where('status', 'published')->count() : 0,
            'applications_count' => $company ? $company->jobs()->withCount('applications')->get()->sum('applications_count') : 0,
        ], 'Recruiter dashboard fetched.');
    }
}
