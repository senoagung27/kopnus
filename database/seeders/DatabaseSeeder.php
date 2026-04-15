<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with MX100 sample data.
     */
    public function run(): void
    {
        $employerA = User::query()->create([
            'name' => 'PT Contoh Sejahtera',
            'email' => 'employer@mx100.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_EMPLOYER,
        ]);

        $employerB = User::query()->create([
            'name' => 'Startup Nusantara',
            'email' => 'hr@startup.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_EMPLOYER,
        ]);

        $freelancer1 = User::query()->create([
            'name' => 'Budi Freelancer',
            'email' => 'budi@freelancer.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_FREELANCER,
        ]);

        $freelancer2 = User::query()->create([
            'name' => 'Siti Expert',
            'email' => 'siti@freelancer.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_FREELANCER,
        ]);

        $draftJob = Job::query()->create([
            'employer_id' => $employerA->id,
            'title' => 'Backend Developer (Draft)',
            'description' => "Lowongan masih disimpan sebagai draf.\nBelum terlihat oleh freelancer.",
            'status' => Job::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $publishedJob1 = Job::query()->create([
            'employer_id' => $employerA->id,
            'title' => 'Laravel API Developer',
            'description' => "Membangun REST API untuk portal job.\nPengalaman Laravel 10+ dan Sanctum.",
            'status' => Job::STATUS_PUBLISHED,
            'published_at' => now()->subDays(2),
        ]);

        $publishedJob2 = Job::query()->create([
            'employer_id' => $employerB->id,
            'title' => 'UI Reviewer Part-time',
            'description' => 'Review komponen UI dan aksesibilitas, remote.',
            'status' => Job::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        JobApplication::query()->create([
            'job_id' => $publishedJob1->id,
            'freelancer_id' => $freelancer1->id,
            'cover_letter' => 'Saya berpengalaman 3 tahun membangun API dengan Laravel.',
            'cv_file_path' => null,
        ]);

        JobApplication::query()->create([
            'job_id' => $publishedJob2->id,
            'freelancer_id' => $freelancer2->id,
            'cover_letter' => 'Spesialis UI/UX dan testing aksesibilitas.',
            'cv_file_path' => null,
        ]);

        $this->command->info('Sample users (password: password):');
        $this->command->table(
            ['Email', 'Role'],
            [
                ['employer@mx100.test', 'employer'],
                ['hr@startup.test', 'employer'],
                ['budi@freelancer.test', 'freelancer'],
                ['siti@freelancer.test', 'freelancer'],
            ]
        );
        $this->command->info("Draft job ID: {$draftJob->id} (not visible to freelancers).");
    }
}
