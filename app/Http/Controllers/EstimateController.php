<?php

namespace App\Http\Controllers;

use App\CQRS\CommandBus;
use App\CQRS\QueryBus;
use App\CQRS\Commands\EstimateCommand;
use App\CQRS\Queries\EstimateQuery;
use App\Models\Estimate;
use App\Services\EstimateCalculator;
use App\Http\Resources\EstimateResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EstimateController extends Controller
{
    public function __construct(
        private readonly CommandBus $command_bus,
        private readonly QueryBus $query_bus,
        private readonly EstimateCalculator $calculator
    ) {}

    /**
     * Display a listing of estimates for a project.
     */
    public function index(Request $request, int $project_id): JsonResponse
    {
        $query = new EstimateQuery(
            type: EstimateQuery::TYPE_LIST,
            tenant_id: $request->user()->tenant_id,
            project_id: $project_id,
            filters: $request->input('filters', []),
            includes: explode(',', $request->input('includes', '')),
            sort_by: $request->input('sort_by'),
            sort_direction: $request->input('sort_direction', 'desc')
        );

        $estimates = $this->query_bus->ask($query);

        return response()->json([
            'status' => 'success',
            'data' => EstimateResource::collection($estimates)
        ]);
    }

    /**
     * Store a newly created estimate.
     */
    public function store(Request $request, int $project_id): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date|after:today',
            'items' => 'array',
            'items.*.category' => 'required|string',
            'items.*.subcategory' => 'nullable|string',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.markup_percentage' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $command = new EstimateCommand(
            type: EstimateCommand::TYPE_CREATE,
            tenant_id: $request->user()->tenant_id,
            user_id: $request->user()->id,
            project_id: $project_id,
            attributes: $validated
        );

        $estimate = $this->command_bus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'Estimate created successfully',
            'data' => new EstimateResource($estimate)
        ], 201);
    }

    /**
     * Display the specified estimate.
     */
    public function show(Request $request, int $project_id, int $estimate_id): JsonResponse
    {
        $query = new EstimateQuery(
            type: EstimateQuery::TYPE_SINGLE,
            tenant_id: $request->user()->tenant_id,
            project_id: $project_id,
            estimate_id: $estimate_id,
            includes: explode(',', $request->input('includes', ''))
        );

        $estimate = $this->query_bus->ask($query);

        return response()->json([
            'status' => 'success',
            'data' => new EstimateResource($estimate)
        ]);
    }

    /**
     * Update the specified estimate.
     */
    public function update(Request $request, int $project_id, int $estimate_id): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date|after:today',
        ]);

        $command = new EstimateCommand(
            type: EstimateCommand::TYPE_UPDATE,
            tenant_id: $request->user()->tenant_id,
            user_id: $request->user()->id,
            estimate_id: $estimate_id,
            attributes: $validated
        );

        $estimate = $this->command_bus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'Estimate updated successfully',
            'data' => new EstimateResource($estimate)
        ]);
    }

    /**
     * Add an item to the estimate.
     */
    public function add_item(Request $request, int $project_id, int $estimate_id): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'subcategory' => 'nullable|string',
            'description' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'unit_price' => 'required|numeric|min:0',
            'markup_percentage' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $command = new EstimateCommand(
            type: EstimateCommand::TYPE_ADD_ITEM,
            tenant_id: $request->user()->tenant_id,
            user_id: $request->user()->id,
            estimate_id: $estimate_id,
            attributes: $validated
        );

        $item = $this->command_bus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'Item added successfully',
            'data' => $item
        ]);
    }

    /**
     * Calculate markup distribution for target total.
     */
    public function calculate_markup(Request $request, int $project_id, int $estimate_id): JsonResponse
    {
        $validated = $request->validate([
            'target_markup_percentage' => 'required|numeric|min:0|max:100'
        ]);

        $estimate = Estimate::findOrFail($estimate_id);
        $markup_adjustments = $this->calculator->calculate_markup_distribution(
            $estimate,
            $validated['target_markup_percentage']
        );

        return response()->json([
            'status' => 'success',
            'data' => $markup_adjustments
        ]);
    }

    /**
     * Approve the estimate.
     */
    public function approve(Request $request, int $project_id, int $estimate_id): JsonResponse
    {
        $command = new EstimateCommand(
            type: EstimateCommand::TYPE_APPROVE,
            tenant_id: $request->user()->tenant_id,
            user_id: $request->user()->id,
            estimate_id: $estimate_id
        );

        $estimate = $this->command_bus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'Estimate approved successfully',
            'data' => new EstimateResource($estimate)
        ]);
    }

    /**
     * Reject the estimate.
     */
    public function reject(Request $request, int $project_id, int $estimate_id): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $command = new EstimateCommand(
            type: EstimateCommand::TYPE_REJECT,
            tenant_id: $request->user()->tenant_id,
            user_id: $request->user()->id,
            estimate_id: $estimate_id,
            attributes: $validated
        );

        $estimate = $this->command_bus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'Estimate rejected successfully',
            'data' => new EstimateResource($estimate)
        ]);
    }

    /**
     * Create a new revision of the estimate.
     */
    public function revise(Request $request, int $project_id, int $estimate_id): JsonResponse
    {
        $command = new EstimateCommand(
            type: EstimateCommand::TYPE_REVISE,
            tenant_id: $request->user()->tenant_id,
            user_id: $request->user()->id,
            estimate_id: $estimate_id
        );

        $estimate = $this->command_bus->dispatch($command);

        return response()->json([
            'status' => 'success',
            'message' => 'New estimate revision created successfully',
            'data' => new EstimateResource($estimate)
        ]);
    }
}
