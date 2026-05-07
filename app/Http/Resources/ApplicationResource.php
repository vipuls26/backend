<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job' => [
                'id' => $this->job?->id,
                'title' => $this->job?->title,
            ],
            'company' => [
                'id' => $this->job?->company?->id,
                'name' => $this->job?->company?->name,
            ],
            'candidate' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'cover_letter' => $this->cover_letter,
            'status' => $this->status,
            'applied_at' => $this->created_at,
        ];
    }
}
