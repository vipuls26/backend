<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'company' => [
                'id' => $this->company?->id,
                'name' => $this->company?->name,
            ],
            'location' => $this->location,
            'type' => $this->type,
            'salary' => $this->salary,
            'experience' => $this->experience,
            'status' => $this->status,
            'has_applied' => $user
                ? $this->applications->contains('user_id', $user->id)
                : false,
            'is_saved' => $user
                ? $this->savedByUsers->contains('id', $user->id)
                : false,
            'created_at' => $this->created_at,
        ];
    }
}
