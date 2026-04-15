<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with MX100 sample data.
     * Aman dijalankan ulang (idempoten) berdasarkan email / kunci unik.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superadmin@mx100.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'role' => User::ROLE_SUPERADMIN,
            ],
        );

        $employerA = User::query()->updateOrCreate(
            ['email' => 'employer@mx100.test'],
            [
                'name' => 'PT Contoh Sejahtera',
                'password' => 'password',
                'role' => User::ROLE_EMPLOYER,
            ],
        );

        $employerB = User::query()->updateOrCreate(
            ['email' => 'hr@startup.test'],
            [
                'name' => 'Startup Nusantara',
                'password' => 'password',
                'role' => User::ROLE_EMPLOYER,
            ],
        );

        $freelancer1 = User::query()->updateOrCreate(
            ['email' => 'budi@freelancer.test'],
            [
                'name' => 'Budi Freelancer',
                'password' => 'password',
                'role' => User::ROLE_FREELANCER,
            ],
        );

        $freelancer2 = User::query()->updateOrCreate(
            ['email' => 'siti@freelancer.test'],
            [
                'name' => 'Siti Expert',
                'password' => 'password',
                'role' => User::ROLE_FREELANCER,
            ],
        );

        $draftJob = Job::query()->updateOrCreate(
            [
                'employer_id' => $employerA->id,
                'title' => 'Backend Developer (Draft)',
            ],
            [
                'description' => "Lowongan masih disimpan sebagai draf.\nBelum terlihat oleh freelancer.",
                'status' => Job::STATUS_DRAFT,
                'published_at' => null,
            ],
        );

        $publishedJob1 = Job::query()->updateOrCreate(
            [
                'employer_id' => $employerA->id,
                'title' => 'Laravel API Developer',
            ],
            [
                'description' => "Membangun REST API untuk portal job.\nPengalaman Laravel 10+ dan Sanctum.",
                'status' => Job::STATUS_PUBLISHED,
                'published_at' => now()->subDays(2),
            ],
        );

        $publishedJob2 = Job::query()->updateOrCreate(
            [
                'employer_id' => $employerB->id,
                'title' => 'UI Reviewer Part-time',
            ],
            [
                'description' => 'Review komponen UI dan aksesibilitas, remote.',
                'status' => Job::STATUS_PUBLISHED,
                'published_at' => now()->subDay(),
            ],
        );

        JobApplication::query()->updateOrCreate(
            [
                'job_id' => $publishedJob1->id,
                'freelancer_id' => $freelancer1->id,
            ],
            [
                'cover_letter' => 'Saya berpengalaman 3 tahun membangun API dengan Laravel.',
                'cv_file_path' => null,
            ],
        );

        JobApplication::query()->updateOrCreate(
            [
                'job_id' => $publishedJob2->id,
                'freelancer_id' => $freelancer2->id,
            ],
            [
                'cover_letter' => 'Spesialis UI/UX dan testing aksesibilitas.',
                'cv_file_path' => null,
            ],
        );

        $this->command->info('Sample users (password: password):');
        $this->command->table(
            ['Email', 'Role'],
            [
                ['superadmin@mx100.test', 'superadmin'],
                ['employer@mx100.test', 'employer'],
                ['hr@startup.test', 'employer'],
                ['budi@freelancer.test', 'freelancer'],
                ['siti@freelancer.test', 'freelancer'],
            ]
        );
        $this->command->info("Draft job ID: {$draftJob->id} (not visible to freelancers).");
    }
}
