<?php

namespace App\Http\Controllers\Api\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JobListingController extends Controller
{
    public function index(): ResourceCollection
    {
        $jobs = Job::query()
            ->published()
            ->orderByDesc('published_at')
            ->paginate(15);

        return JobResource::collection($jobs)->additional([
            'message' => 'OK',
        ]);
    }

    public function show(Job $job): JsonResponse
    {
        if (! $job->isPublished()) {
            abort(404);
        }

        return response()->json([
            'message' => 'OK',
            'data' => new JobResource($job),
        ]);
    }
}
