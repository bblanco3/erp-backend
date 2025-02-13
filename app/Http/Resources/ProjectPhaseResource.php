<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectPhaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'progress' => $this->progress,
            'dependencies' => $this->whenLoaded('dependencies', function () {
                return $this->dependencies->pluck('id');
            }),
            'milestones' => ProjectMilestoneResource::collection($this->whenLoaded('milestones')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
