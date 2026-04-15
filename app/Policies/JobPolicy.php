<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEmployer();
    }

    public function view(User $user, Job $job): bool
    {
        if ($user->isEmployer()) {
            return $job->employer_id === $user->id;
        }

        return $user->isFreelancer() && $job->isPublished();
    }

    public function create(User $user): bool
    {
        return $user->isEmployer();
    }

    public function update(User $user, Job $job): bool
    {
        return $user->isEmployer() && $job->employer_id === $user->id;
    }

    public function delete(User $user, Job $job): bool
    {
        return $user->isEmployer() && $job->employer_id === $user->id;
    }

    public function publish(User $user, Job $job): bool
    {
        return $this->update($user, $job);
    }

    /**
     * Employer may list CVs / applications for their job posting.
     */
    public function viewApplications(User $user, Job $job): bool
    {
        return $user->isEmployer() && $job->employer_id === $user->id;
    }
}
