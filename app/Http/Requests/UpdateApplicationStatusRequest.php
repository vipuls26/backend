<?php

namespace App\Http\Requests;

use App\Support\ValueNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRecruiter() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => ValueNormalizer::enumLike($this->input('status')),
        ]);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['pending', 'interview_scheduled', 'accepted', 'rejected'])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select an application status.',
            'status.in' => 'Application status must be pending, interview_scheduled, accepted, or rejected.',
        ];
    }
}
