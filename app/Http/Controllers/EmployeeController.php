<?php

namespace App\Http\Controllers;

use App\CQRS\CommandBus;
use App\CQRS\QueryBus;
use App\CQRS\Commands\EmployeeCommand;
use App\CQRS\Queries\EmployeeQuery;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\EmployeeResource;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus
    ) {}

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): JsonResponse
    {
        $query = new EmployeeQuery(
            type: EmployeeQuery::TYPE_LIST,
            tenantId: $request->user()->tenant_id,
            filters: $request->input('filters', []),
            includes: explode(',', $request->input('includes', '')),
            searchTerm: $request->input('search'),
            sortBy: $request->input('sort_by'),
            sortDirection: $request->input('sort_direction', 'asc'),
            limit: $request->input('limit')
        );

        $employees = $this->queryBus->ask($query);

        return response()->json([
            'status' => 'success',
            'data' => EmployeeResource::collection($employees)
        ]);
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $command = new EmployeeCommand(
            type: EmployeeCommand::TYPE_CREATE,
            tenantId: $request->user()->tenant_id,
            userId: $request->user()->id,
            attributes: $request->validated()
        );

        $employee = $this->commandBus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'Employee created successfully',
            'data' => new EmployeeResource($employee)
        ], 201);
    }

    /**
     * Display the specified employee.
     */
    public function show(Request $request, Employee $employee): JsonResponse
    {
        $query = new EmployeeQuery(
            type: EmployeeQuery::TYPE_SINGLE,
            tenantId: $request->user()->tenant_id,
            employeeId: $employee->id,
            includes: explode(',', $request->input('includes', ''))
        );

        $employeeData = $this->queryBus->ask($query);

        return response()->json([
            'status' => 'success',
            'data' => new EmployeeResource($employeeData)
        ]);
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $command = new EmployeeCommand(
            type: EmployeeCommand::TYPE_UPDATE,
            tenantId: $request->user()->tenant_id,
            userId: $request->user()->id,
            employeeId: $employee->id,
            attributes: $request->validated()
        );

        $employee = $this->commandBus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'Employee updated successfully',
            'data' => new EmployeeResource($employee)
        ]);
    }

    /**
     * Soft delete the specified employee.
     */
    public function destroy(Request $request, Employee $employee): JsonResponse
    {
        $command = new EmployeeCommand(
            type: EmployeeCommand::TYPE_DELETE,
            tenantId: $request->user()->tenant_id,
            userId: $request->user()->id,
            employeeId: $employee->id
        );

        $this->commandBus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'Employee deleted successfully'
        ]);
    }

    /**
     * Get employee time entries.
     */
    public function timeEntries(Request $request, Employee $employee): JsonResponse
    {
        $query = new EmployeeQuery(
            type: EmployeeQuery::TYPE_TIME_ENTRIES,
            tenantId: $request->user()->tenant_id,
            employeeId: $employee->id,
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date')
        );

        $timeEntries = $this->queryBus->ask($query);

        return response()->json([
            'status' => 'success',
            'data' => $timeEntries
        ]);
    }

    /**
     * Clock in/out an employee.
     */
    public function clockInOut(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out,break_start,break_end',
            'time' => 'required|date_format:Y-m-d H:i:s',
            'notes' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id'
        ]);

        $command = new EmployeeCommand(
            type: EmployeeCommand::TYPE_CLOCK,
            tenantId: $request->user()->tenant_id,
            userId: $request->user()->id,
            employeeId: $employee->id,
            attributes: $validated
        );

        $timeEntry = $this->commandBus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'Time entry recorded successfully',
            'data' => $timeEntry
        ]);
    }

    /**
     * Get employee schedule.
     */
    public function schedule(Request $request, Employee $employee): JsonResponse
    {
        $query = new EmployeeQuery(
            type: EmployeeQuery::TYPE_SCHEDULE,
            tenantId: $request->user()->tenant_id,
            employeeId: $employee->id,
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date')
        );

        $schedule = $this->queryBus->ask($query);

        return response()->json([
            'status' => 'success',
            'data' => $schedule
        ]);
    }

    /**
     * Update employee schedule.
     */
    public function updateSchedule(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'regular_hours' => 'required|array',
            'regular_hours.start' => 'required|date_format:H:i',
            'regular_hours.end' => 'required|date_format:H:i|after:regular_hours.start',
            'days_off' => 'required|array',
            'days_off.*' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ]);

        $command = new EmployeeCommand(
            type: EmployeeCommand::TYPE_UPDATE_SCHEDULE,
            tenantId: $request->user()->tenant_id,
            userId: $request->user()->id,
            employeeId: $employee->id,
            attributes: $validated
        );

        $schedule = $this->commandBus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule updated successfully',
            'data' => $schedule
        ]);
    }
}
