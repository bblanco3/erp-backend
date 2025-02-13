<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'priority' => $this->priority,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'target_date' => $this->target_date,
            'completion_date' => $this->completion_date,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'address' => [
                'street' => $this->address_street,
                'city' => $this->address_city,
                'state' => $this->address_state,
                'zip' => $this->address_zip,
                'country' => $this->address_country,
            ],
            'budget' => [
                'estimated' => $this->budget_estimated,
                'actual' => $this->budget_actual,
                'materials' => $this->budget_materials,
                'labor' => $this->budget_labor,
                'overhead' => $this->budget_overhead,
            ],
            'progress' => $this->progress,
            'phases' => ProjectPhaseResource::collection($this->whenLoaded('phases')),
            'team' => ProjectTeamMemberResource::collection($this->whenLoaded('team')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'change_orders' => ChangeOrderResource::collection($this->whenLoaded('changeOrders')),
            'created_by' => new UserResource($this->whenLoaded('creator')),
            'updated_by' => new UserResource($this->whenLoaded('updater')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
