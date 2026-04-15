<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        return [
            'employer_id' => User::factory()->state(['role' => User::ROLE_EMPLOYER]),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'status' => Job::STATUS_DRAFT,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Job::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }
}
