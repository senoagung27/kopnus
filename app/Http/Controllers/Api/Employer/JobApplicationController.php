<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobApplicationResource;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index(Request $request, Job $job): JsonResponse
    {
        $this->authorize('viewApplications', $job);

        $applications = $job->applications()
            ->with('freelancer')
            ->orderByDesc('created_at')
            ->paginate(15);

        return JobApplicationResource::collection($applications)->additional([
            'message' => 'OK',
        ]);
    }
}
