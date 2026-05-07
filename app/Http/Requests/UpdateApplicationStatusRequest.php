<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRecruiter() === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['Pending', 'Interview Scheduled', 'Accepted', 'Rejected'])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select an application status.',
            'status.in' => 'Application status must be Pending, Interview Scheduled, Accepted, or Rejected.',
        ];
    }
}
