<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $companies = Company::query()
            ->whereHas('jobs', fn ($query) => $query->where('status', 'published'))
            ->latest()
            ->get();

        return $this->success(CompanyResource::collection($companies)->resolve($request), 'Companies fetched.');
    }
}
