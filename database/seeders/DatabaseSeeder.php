<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Company;
use App\Models\Job;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $recruiter = User::updateOrCreate(
            ['email' => 'recruiter@example.com'],
            [
                'name' => 'Demo Recruiter',
                'password' => Hash::make('password'),
                'role' => 'recruiter',
            ]
        );

        $candidate = User::updateOrCreate(
            ['email' => 'candidate@example.com'],
            [
                'name' => 'Demo Candidate',
                'password' => Hash::make('password'),
                'role' => 'candidate',
            ]
        );

        $secondCandidate = User::updateOrCreate(
            ['email' => 'ananya@example.com'],
            [
                'name' => 'Ananya Patel',
                'password' => Hash::make('password'),
                'role' => 'candidate',
            ]
        );

        $company = Company::updateOrCreate(
            ['user_id' => $recruiter->id],
            [
                'name' => 'Pixel Labs',
                'industry' => 'Software',
                'location' => 'Ahmedabad',
                'website' => 'https://pixellabs.test',
                'description' => 'A product engineering company building modern web platforms.',
            ]
        );

        $frontendJob = Job::updateOrCreate(
            ['company_id' => $company->id, 'title' => 'Frontend Developer'],
            [
                'location' => 'Remote',
                'type' => 'Full Time',
                'salary' => '$4k - $7k',
                'experience' => '2+ years',
                'status' => 'published',
            ]
        );

        $backendJob = Job::updateOrCreate(
            ['company_id' => $company->id, 'title' => 'Laravel Backend Developer'],
            [
                'location' => 'Ahmedabad',
                'type' => 'Full Time',
                'salary' => '$5k - $8k',
                'experience' => '3+ years',
                'status' => 'published',
            ]
        );

        Job::updateOrCreate(
            ['company_id' => $company->id, 'title' => 'UI Design Intern'],
            [
                'location' => 'Hybrid',
                'type' => 'Internship',
                'salary' => '$800 - $1.2k',
                'experience' => 'Fresher',
                'status' => 'published',
            ]
        );

        Job::updateOrCreate(
            ['company_id' => $company->id, 'title' => 'DevOps Engineer'],
            [
                'location' => 'Remote',
                'type' => 'Contract',
                'salary' => '$6k - $9k',
                'experience' => '4+ years',
                'status' => 'draft',
            ]
        );

        Application::updateOrCreate(
            ['job_id' => $frontendJob->id, 'user_id' => $candidate->id],
            [
                'full_name' => $candidate->name,
                'email' => $candidate->email,
                'cover_letter' => 'I am interested in building clean, responsive frontend experiences for Pixel Labs.',
                'status' => 'Pending',
            ]
        );

        Application::updateOrCreate(
            ['job_id' => $backendJob->id, 'user_id' => $secondCandidate->id],
            [
                'full_name' => $secondCandidate->name,
                'email' => $secondCandidate->email,
                'cover_letter' => 'I have Laravel API experience and would love to contribute to your backend team.',
                'status' => 'Interview Scheduled',
            ]
        );

        $candidate->savedJobs()->syncWithoutDetaching([$frontendJob->id, $backendJob->id]);

        PortalNotification::updateOrCreate(
            [
                'user_id' => $secondCandidate->id,
                'title' => 'Interview scheduled',
                'message' => 'Your application for Laravel Backend Developer at Pixel Labs was moved to interview scheduled.',
            ],
            ['read_at' => null]
        );
    }
}
