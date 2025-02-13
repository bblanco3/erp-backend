<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstimateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'estimate_number' => $this->estimate_number,
            'status' => $this->status,
            'version' => $this->version,
            'total_cost' => $this->total_cost,
            'total_markup' => $this->total_markup,
            'total_price' => $this->total_price,
            'notes' => $this->notes,
            'valid_until' => $this->valid_until,
            'items' => EstimateItemResource::collection($this->whenLoaded('items')),
            'approved_by' => new UserResource($this->whenLoaded('approver')),
            'approved_at' => $this->approved_at,
            'created_by' => new UserResource($this->whenLoaded('creator')),
            'updated_by' => new UserResource($this->whenLoaded('updater')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
