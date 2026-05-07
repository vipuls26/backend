<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRecruiter() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'industry' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the company name.',
            'name.max' => 'Company name cannot be longer than 255 characters.',
            'industry.required' => 'Please enter the company industry.',
            'industry.max' => 'Industry cannot be longer than 255 characters.',
            'location.required' => 'Please enter the company location.',
            'location.max' => 'Location cannot be longer than 255 characters.',
            'website.url' => 'Please enter a valid website URL.',
            'website.max' => 'Website cannot be longer than 255 characters.',
            'description.string' => 'Company description must be text.',
        ];
    }
}
