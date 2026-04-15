<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;

class JobService
{
    public function create(User $employer, array $data): Job
    {
        return $employer->postedJobs()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'] ?? Job::STATUS_DRAFT,
            'published_at' => ($data['status'] ?? Job::STATUS_DRAFT) === Job::STATUS_PUBLISHED
                ? now()
                : null,
        ]);
    }

    public function update(Job $job, array $data): Job
    {
        $status = $data['status'] ?? $job->status;

        $job->fill([
            'title' => $data['title'] ?? $job->title,
            'description' => $data['description'] ?? $job->description,
            'status' => $status,
        ]);

        if ($status === Job::STATUS_PUBLISHED && ! $job->published_at) {
            $job->published_at = now();
        }

        if ($status === Job::STATUS_DRAFT) {
            $job->published_at = null;
        }

        $job->save();

        return $job->fresh();
    }

    public function publish(Job $job): Job
    {
        $job->update([
            'status' => Job::STATUS_PUBLISHED,
            'published_at' => $job->published_at ?? now(),
        ]);

        return $job->fresh();
    }
}
