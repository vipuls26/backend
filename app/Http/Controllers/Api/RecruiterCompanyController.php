<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RecruiterCompanyController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        $company = $request->user()->company;

        if (! $company) {
            return $this->success(null, 'Company not created yet.');
        }

        return $this->success((new CompanyResource($company))->resolve($request), 'Company fetched.');
    }

    public function store(StoreCompanyRequest $request)
    {
        if ($request->user()->company()->exists()) {
            throw ValidationException::withMessages([
                'company' => ['Recruiter can create only one company.'],
            ]);
        }

        $company = $request->user()->company()->create($request->validated());

        return $this->success((new CompanyResource($company))->resolve($request), 'Company created.', 201);
    }
}
