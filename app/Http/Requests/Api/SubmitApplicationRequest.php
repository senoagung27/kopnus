<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SubmitApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cover_letter' => ['required', 'string'],
            'cv' => ['sometimes', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }
}
