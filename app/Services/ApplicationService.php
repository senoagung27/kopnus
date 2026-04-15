<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    /**
     * @param  array{cover_letter: string, cv_file_path?: string|null}  $data
     */
    public function submit(User $freelancer, Job $job, array $data): JobApplication
    {
        if (! $job->isPublished()) {
            throw ValidationException::withMessages([
                'job' => ['This job is not accepting applications.'],
            ]);
        }

        $exists = JobApplication::query()
            ->where('job_id', $job->id)
            ->where('freelancer_id', $freelancer->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'job' => ['You have already submitted a CV for this job.'],
            ]);
        }

        return JobApplication::create([
            'job_id' => $job->id,
            'freelancer_id' => $freelancer->id,
            'cover_letter' => $data['cover_letter'],
            'cv_file_path' => $data['cv_file_path'] ?? null,
        ]);
    }
}
