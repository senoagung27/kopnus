<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'freelancer_id' => User::factory()->state(['role' => User::ROLE_FREELANCER]),
            'cover_letter' => fake()->paragraph(),
            'cv_file_path' => null,
        ];
    }
}
