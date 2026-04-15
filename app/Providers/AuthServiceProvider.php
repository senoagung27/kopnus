<?php

namespace App\Providers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Policies\JobApplicationPolicy;
use App\Policies\JobPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Job::class => JobPolicy::class,
        JobApplication::class => JobApplicationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function (?User $user): ?bool {
            if ($user?->isSuperadmin()) {
                return true;
            }

            return null;
        });
    }
}
