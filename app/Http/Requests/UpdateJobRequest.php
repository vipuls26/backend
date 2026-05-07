<?php

namespace App\Http\Requests;

use App\Support\ValueNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRecruiter() === true;
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        if ($this->has('type')) {
            $normalized['type'] = ValueNormalizer::enumLike($this->input('type'));
        }

        if ($this->has('status')) {
            $normalized['status'] = ValueNormalizer::enumLike($this->input('status'));
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', Rule::in(['full_time', 'part_time', 'contract', 'internship'])],
            'salary' => ['sometimes', 'required', 'string', 'max:255'],
            'experience' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'published'])],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter the job title.',
            'title.max' => 'Job title cannot be longer than 255 characters.',
            'location.required' => 'Please enter the job location.',
            'location.max' => 'Job location cannot be longer than 255 characters.',
            'type.required' => 'Please select the job type.',
            'type.in' => 'Job type must be full_time, part_time, contract, or internship.',
            'salary.required' => 'Please enter the salary range.',
            'salary.max' => 'Salary cannot be longer than 255 characters.',
            'experience.required' => 'Please enter the experience requirement.',
            'experience.max' => 'Experience cannot be longer than 255 characters.',
            'status.required' => 'Please select the job status.',
            'status.in' => 'Job status must be draft or published.',
        ];
    }
}
