<?php

namespace App\Http\Controllers\Api\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubmitApplicationRequest;
use App\Http\Resources\JobApplicationResource;
use App\Models\Job;
use App\Models\JobApplication;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;

class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applicationService
    ) {}

    public function store(SubmitApplicationRequest $request, Job $job): JsonResponse
    {
        $this->authorize('create', [JobApplication::class, $job]);

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cvs', 'public');
        }

        $application = $this->applicationService->submit($request->user(), $job, [
            'cover_letter' => $request->validated('cover_letter'),
            'cv_file_path' => $cvPath,
        ]);

        $application->load('freelancer');

        return response()->json([
            'message' => 'Application submitted.',
            'data' => new JobApplicationResource($application),
        ], 201);
    }
}
