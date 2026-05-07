<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CandidateDashboardController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RecruiterApplicationController;
use App\Http\Controllers\Api\RecruiterCompanyController;
use App\Http\Controllers\Api\RecruiterDashboardController;
use App\Http\Controllers\Api\RecruiterJobController;
use App\Http\Controllers\Api\SavedJobController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed:relative')
    ->name('verification.verify');

Route::middleware('token.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/profile', [AuthController::class, 'updateProfile']);
    Route::get('/verify-token', [AuthController::class, 'verifyToken']);
    Route::post('/email/verification-notification', [AuthController::class, 'resendEmailVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read', [NotificationController::class, 'markRead']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markOneRead']);
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
});

Route::middleware(['token.auth', 'role:candidate'])->group(function () {
    Route::get('/candidate/dashboard', CandidateDashboardController::class);
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store']);
    Route::post('/jobs/{job}/save', [SavedJobController::class, 'store']);
    Route::delete('/jobs/{job}/save', [SavedJobController::class, 'destroy']);
});

Route::middleware(['token.auth', 'role:recruiter'])->prefix('recruiter')->group(function () {
    Route::get('/dashboard', RecruiterDashboardController::class);
    Route::get('/company', [RecruiterCompanyController::class, 'show']);
    Route::post('/company', [RecruiterCompanyController::class, 'store']);
    Route::get('/jobs', [RecruiterJobController::class, 'index']);
    Route::post('/jobs', [RecruiterJobController::class, 'store']);
    Route::patch('/jobs/{job}', [RecruiterJobController::class, 'update']);
    Route::patch('/jobs/{job}/status', [RecruiterJobController::class, 'updateStatus']);
    Route::get('/applications', [RecruiterApplicationController::class, 'index']);
    Route::patch('/applications/{application}/status', [RecruiterApplicationController::class, 'updateStatus']);
});
