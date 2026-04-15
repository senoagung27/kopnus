<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function create(User $user, Job $job): bool
    {
        return $user->isFreelancer() && $job->isPublished();
    }
}
