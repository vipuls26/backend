<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRecruiter() === true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['Full Time', 'Part Time', 'Contract', 'Internship'])],
            'salary' => ['required', 'string', 'max:255'],
            'experience' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published'])],
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
            'type.in' => 'Job type must be Full Time, Part Time, Contract, or Internship.',
            'salary.required' => 'Please enter the salary range.',
            'salary.max' => 'Salary cannot be longer than 255 characters.',
            'experience.required' => 'Please enter the experience requirement.',
            'experience.max' => 'Experience cannot be longer than 255 characters.',
            'status.required' => 'Please select the job status.',
            'status.in' => 'Job status must be draft or published.',
        ];
    }
}
