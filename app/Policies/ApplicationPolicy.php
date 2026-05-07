<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewCandidate(User $user, Application $application): bool
    {
        return $user->isCandidate() && $application->user_id === $user->id;
    }

    public function review(User $user, Application $application): bool
    {
        return $user->isRecruiter()
            && $user->company
            && $application->job
            && $application->job->company_id === $user->company->id;
    }
}
