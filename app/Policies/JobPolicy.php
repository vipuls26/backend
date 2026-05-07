<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function update(User $user, Job $job): bool
    {
        return $user->isRecruiter()
            && $user->company
            && $job->company_id === $user->company->id;
    }

    public function publish(User $user, Job $job): bool
    {
        return $this->update($user, $job);
    }

    public function apply(User $user, Job $job): bool
    {
        return $user->isCandidate() && $job->status === 'published';
    }
}
