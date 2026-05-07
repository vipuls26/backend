<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class SavedJobController extends Controller
{
    use ApiResponse;

    public function store(Request $request, Job $job)
    {
        if ($job->status !== 'published') {
            abort(403, 'You can only save published jobs.');
        }

        $request->user()->savedJobs()->syncWithoutDetaching([$job->id]);

        return $this->success(null, 'Job saved.', 201);
    }

    public function destroy(Request $request, Job $job)
    {
        $request->user()->savedJobs()->detach($job->id);

        return $this->success(null, 'Job removed from saved jobs.');
    }
}
