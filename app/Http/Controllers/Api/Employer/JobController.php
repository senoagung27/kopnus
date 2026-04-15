<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreJobRequest;
use App\Http\Requests\Api\UpdateJobRequest;
use App\Http\Resources\JobResource;
use App\Models\Job;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JobController extends Controller
{
    public function __construct(
        private readonly JobService $jobService
    ) {}

    public function index(Request $request): ResourceCollection
    {
        $this->authorize('viewAny', Job::class);

        $jobs = Job::query()
            ->where('employer_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->paginate(15);

        return JobResource::collection($jobs)->additional([
            'message' => 'OK',
        ]);
    }

    public function store(StoreJobRequest $request): JsonResponse
    {
        $this->authorize('create', Job::class);

        $job = $this->jobService->create($request->user(), $request->validated());

        return response()->json([
            'message' => 'Job created.',
            'data' => new JobResource($job),
        ], 201);
    }

    public function show(Request $request, Job $job): JsonResponse
    {
        $this->authorize('view', $job);

        return response()->json([
            'message' => 'OK',
            'data' => new JobResource($job),
        ]);
    }

    public function update(UpdateJobRequest $request, Job $job): JsonResponse
    {
        $this->authorize('update', $job);

        $job = $this->jobService->update($job, $request->validated());

        return response()->json([
            'message' => 'Job updated.',
            'data' => new JobResource($job),
        ]);
    }

    public function destroy(Request $request, Job $job): JsonResponse
    {
        $this->authorize('delete', $job);

        $job->delete();

        return response()->json([
            'message' => 'Job deleted.',
            'data' => null,
        ]);
    }

    public function publish(Request $request, Job $job): JsonResponse
    {
        $this->authorize('publish', $job);

        $job = $this->jobService->publish($job);

        return response()->json([
            'message' => 'Job published.',
            'data' => new JobResource($job),
        ]);
    }
}
