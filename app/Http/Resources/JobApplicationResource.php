<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\JobApplication
 */
class JobApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_id' => $this->job_id,
            'freelancer' => new UserResource($this->whenLoaded('freelancer')),
            'cover_letter' => $this->cover_letter,
            'cv_file_path' => $this->cv_file_path,
            'cv_url' => $this->when(
                $this->cv_file_path !== null,
                fn () => Storage::disk('public')->url($this->cv_file_path)
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
