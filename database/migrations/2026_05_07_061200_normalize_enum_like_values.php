<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE applications MODIFY status VARCHAR(50) NOT NULL DEFAULT "pending"');
        }

        DB::table('applications')->where('status', 'Pending')->update(['status' => 'pending']);
        DB::table('applications')->where('status', 'Interview Scheduled')->update(['status' => 'interview_scheduled']);
        DB::table('applications')->where('status', 'Accepted')->update(['status' => 'accepted']);
        DB::table('applications')->where('status', 'Rejected')->update(['status' => 'rejected']);

        DB::table('jobs')->where('type', 'Full Time')->update(['type' => 'full_time']);
        DB::table('jobs')->where('type', 'Part Time')->update(['type' => 'part_time']);
        DB::table('jobs')->where('type', 'Contract')->update(['type' => 'contract']);
        DB::table('jobs')->where('type', 'Internship')->update(['type' => 'internship']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE applications MODIFY status ENUM('pending','interview_scheduled','accepted','rejected') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE applications MODIFY status VARCHAR(50) NOT NULL DEFAULT "Pending"');
        }

        DB::table('applications')->where('status', 'pending')->update(['status' => 'Pending']);
        DB::table('applications')->where('status', 'interview_scheduled')->update(['status' => 'Interview Scheduled']);
        DB::table('applications')->where('status', 'accepted')->update(['status' => 'Accepted']);
        DB::table('applications')->where('status', 'rejected')->update(['status' => 'Rejected']);

        DB::table('jobs')->where('type', 'full_time')->update(['type' => 'Full Time']);
        DB::table('jobs')->where('type', 'part_time')->update(['type' => 'Part Time']);
        DB::table('jobs')->where('type', 'contract')->update(['type' => 'Contract']);
        DB::table('jobs')->where('type', 'internship')->update(['type' => 'Internship']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE applications MODIFY status ENUM('Pending','Interview Scheduled','Accepted','Rejected') NOT NULL DEFAULT 'Pending'");
        }
    }
};
