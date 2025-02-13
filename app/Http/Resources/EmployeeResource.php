<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'role' => $this->role,
            'department' => $this->department,
            'hourly_rate' => $this->hourly_rate,
            'start_date' => $this->start_date,
            'emergency_contact' => [
                'name' => $this->emergency_contact_name,
                'phone' => $this->emergency_contact_phone,
                'relationship' => $this->emergency_contact_relationship,
            ],
            'schedule' => [
                'regular_hours' => [
                    'start' => $this->schedule_start,
                    'end' => $this->schedule_end,
                ],
                'days_off' => $this->days_off,
            ],
            'skills' => $this->skills,
            'certifications' => $this->whenLoaded('certifications', function () {
                return $this->certifications->map(function ($cert) {
                    return [
                        'name' => $cert->name,
                        'issued_date' => $cert->issued_date,
                        'expiry_date' => $cert->expiry_date,
                    ];
                });
            }),
            'time_entries' => TimeEntryResource::collection($this->whenLoaded('timeEntries')),
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'created_by' => new UserResource($this->whenLoaded('creator')),
            'updated_by' => new UserResource($this->whenLoaded('updater')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
