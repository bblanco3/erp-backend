<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'break_start' => $this->break_start,
            'break_end' => $this->break_end,
            'total_hours' => $this->total_hours,
            'project_id' => $this->project_id,
            'project' => $this->when($this->project_id, function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'number' => $this->project->number,
                ];
            }),
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
