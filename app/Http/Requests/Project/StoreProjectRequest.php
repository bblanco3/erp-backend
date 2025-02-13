<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:50|unique:projects,number',
            'type' => 'required|string|in:residential,commercial',
            'status' => 'required|string|in:lead,active,on-hold,completed,cancelled',
            'priority' => 'required|string|in:low,medium,high',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'target_date' => 'required|date|after:start_date',
            'customer_id' => 'required|exists:customers,id',
            'address' => 'required|array',
            'address.street' => 'required|string|max:255',
            'address.city' => 'required|string|max:100',
            'address.state' => 'required|string|max:100',
            'address.zip' => 'required|string|max:20',
            'address.country' => 'required|string|max:100',
            'budget' => 'required|array',
            'budget.estimated' => 'required|numeric|min:0',
            'budget.materials' => 'required|numeric|min:0',
            'budget.labor' => 'required|numeric|min:0',
            'budget.overhead' => 'required|numeric|min:0',
            'team' => 'nullable|array',
            'team.*.employee_id' => 'required|exists:employees,id',
            'team.*.role' => 'required|string|max:100',
            'team.*.hours_per_week' => 'required|numeric|min:0|max:168',
            'team.*.is_lead' => 'required|boolean',
            'phases' => 'nullable|array',
            'phases.*.name' => 'required|string|max:255',
            'phases.*.start_date' => 'required|date',
            'phases.*.end_date' => 'required|date|after:phases.*.start_date',
            'phases.*.dependencies' => 'nullable|array',
            'phases.*.dependencies.*' => 'exists:project_phases,id',
            'phases.*.milestones' => 'nullable|array',
            'phases.*.milestones.*.name' => 'required|string|max:255',
            'phases.*.milestones.*.due_date' => 'required|date',
            'phases.*.milestones.*.description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'number.unique' => 'This project number is already in use.',
            'target_date.after' => 'The target date must be after the start date.',
            'phases.*.end_date.after' => 'The phase end date must be after its start date.',
            'budget.*.min' => 'Budget values cannot be negative.',
            'team.*.hours_per_week.max' => 'Hours per week cannot exceed 168 (7 days * 24 hours).',
        ];
    }
}
