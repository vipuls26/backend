<?php

namespace App\Http\Requests;

use App\Support\ValueNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobStatusRequest extends FormRequest
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
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select the job status.',
            'status.in' => 'Job status must be draft or published.',
        ];
    }
}
