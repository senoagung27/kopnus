<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Mx100ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login_return_token(): void
    {
        $register = $this->postJson('/api/v1/register', [
            'name' => 'Test Employer',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_EMPLOYER,
        ]);

        $register->assertCreated();
        $register->assertJsonPath('data.user.email', 'new@example.com');

        $login = $this->postJson('/api/v1/login', [
            'email' => 'new@example.com',
            'password' => 'password123',
        ]);

        $login->assertOk();
        $login->assertJsonStructure(['data' => ['token']]);
    }

    public function test_freelancer_job_list_only_includes_published(): void
    {
        $employer = User::factory()->employer()->create();
        $published = Job::factory()->for($employer, 'employer')->published()->create();
        Job::factory()->for($employer, 'employer')->create([
            'status' => Job::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $freelancer = User::factory()->freelancer()->create();
        Sanctum::actingAs($freelancer);

        $response = $this->getJson('/api/v1/jobs');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($published->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_freelancer_gets_404_for_unpublished_job(): void
    {
        $employer = User::factory()->employer()->create();
        $draft = Job::factory()->for($employer, 'employer')->create([
            'status' => Job::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $freelancer = User::factory()->freelancer()->create();
        Sanctum::actingAs($freelancer);

        $this->getJson("/api/v1/jobs/{$draft->id}")->assertNotFound();
    }

    public function test_freelancer_can_apply_once_per_job(): void
    {
        $employer = User::factory()->employer()->create();
        $job = Job::factory()->for($employer, 'employer')->published()->create();
        $freelancer = User::factory()->freelancer()->create();
        Sanctum::actingAs($freelancer);

        $first = $this->postJson("/api/v1/jobs/{$job->id}/applications", [
            'cover_letter' => 'Halo, saya tertarik.',
        ]);
        $first->assertCreated();

        $second = $this->postJson("/api/v1/jobs/{$job->id}/applications", [
            'cover_letter' => 'Kirim lagi.',
        ]);
        $second->assertStatus(422);
        $second->assertJsonValidationErrors(['job']);
    }

    public function test_employer_can_list_applications_for_own_job(): void
    {
        $employer = User::factory()->employer()->create();
        $job = Job::factory()->for($employer, 'employer')->published()->create();
        $freelancer = User::factory()->freelancer()->create();
        JobApplication::factory()->create([
            'job_id' => $job->id,
            'freelancer_id' => $freelancer->id,
            'cover_letter' => 'Saya kandidat.',
        ]);

        Sanctum::actingAs($employer);

        $response = $this->getJson("/api/v1/employer/jobs/{$job->id}/applications");

        $response->assertOk();
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('data.0.cover_letter', 'Saya kandidat.');
    }

    public function test_employer_cannot_view_other_employer_job(): void
    {
        $a = User::factory()->employer()->create();
        $b = User::factory()->employer()->create();
        $job = Job::factory()->for($b, 'employer')->create();

        Sanctum::actingAs($a);

        $this->getJson("/api/v1/employer/jobs/{$job->id}")->assertForbidden();
    }

    public function test_publish_endpoint_sets_published_state(): void
    {
        $employer = User::factory()->employer()->create();
        $job = Job::factory()->for($employer, 'employer')->create([
            'status' => Job::STATUS_DRAFT,
            'published_at' => null,
        ]);

        Sanctum::actingAs($employer);

        $response = $this->postJson("/api/v1/employer/jobs/{$job->id}/publish");

        $response->assertOk();
        $response->assertJsonPath('data.status', Job::STATUS_PUBLISHED);
        $this->assertNotNull($response->json('data.published_at'));
    }
}
