<?php

namespace App\CQRS\Handlers;

use App\CQRS\Commands\EstimateCommand;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Services\EstimateCalculator;
use Illuminate\Support\Facades\DB;

class EstimateCommandHandler
{
    public function __construct(
        private readonly EstimateCalculator $calculator
    ) {}

    public function handle(EstimateCommand $command): mixed
    {
        return match($command->type) {
            EstimateCommand::TYPE_CREATE => $this->handle_create($command),
            EstimateCommand::TYPE_UPDATE => $this->handle_update($command),
            EstimateCommand::TYPE_DELETE => $this->handle_delete($command),
            EstimateCommand::TYPE_ADD_ITEM => $this->handle_add_item($command),
            EstimateCommand::TYPE_UPDATE_ITEM => $this->handle_update_item($command),
            EstimateCommand::TYPE_DELETE_ITEM => $this->handle_delete_item($command),
            EstimateCommand::TYPE_APPROVE => $this->handle_approve($command),
            EstimateCommand::TYPE_REJECT => $this->handle_reject($command),
            EstimateCommand::TYPE_REVISE => $this->handle_revise($command),
            default => throw new \InvalidArgumentException("Invalid command type: {$command->type}")
        };
    }

    private function handle_create(EstimateCommand $command): Estimate
    {
        return DB::transaction(function () use ($command) {
            $estimate = new Estimate();
            $estimate->project_id = $command->project_id;
            $estimate->tenant_id = $command->tenant_id;
            $estimate->estimate_number = $this->generate_estimate_number($command->project_id);
            $estimate->status = 'draft';
            $estimate->version = 1;
            $estimate->notes = $command->attributes['notes'] ?? '';
            $estimate->valid_until = $command->attributes['valid_until'] ?? now()->addDays(30);
            $estimate->created_by_id = $command->user_id;
            $estimate->updated_by_id = $command->user_id;
            $estimate->save();

            if (!empty($command->attributes['items'])) {
                foreach ($command->attributes['items'] as $item_data) {
                    $this->create_estimate_item($estimate, $item_data);
                }
            }

            $this->calculator->recalculate_totals($estimate);

            return $estimate->fresh(['items']);
        });
    }

    private function handle_update(EstimateCommand $command): Estimate
    {
        return DB::transaction(function () use ($command) {
            $estimate = Estimate::findOrFail($command->estimate_id);
            
            if ($estimate->status !== 'draft' && $estimate->status !== 'revised') {
                throw new \RuntimeException('Cannot update a finalized estimate');
            }

            $estimate->notes = $command->attributes['notes'] ?? $estimate->notes;
            $estimate->valid_until = $command->attributes['valid_until'] ?? $estimate->valid_until;
            $estimate->updated_by_id = $command->user_id;
            $estimate->save();

            $this->calculator->recalculate_totals($estimate);

            return $estimate->fresh(['items']);
        });
    }

    private function handle_add_item(EstimateCommand $command): EstimateItem
    {
        return DB::transaction(function () use ($command) {
            $estimate = Estimate::findOrFail($command->estimate_id);
            
            if ($estimate->status !== 'draft' && $estimate->status !== 'revised') {
                throw new \RuntimeException('Cannot add items to a finalized estimate');
            }

            $item = $this->create_estimate_item($estimate, $command->attributes);
            $this->calculator->recalculate_totals($estimate);

            return $item;
        });
    }

    private function handle_update_item(EstimateCommand $command): EstimateItem
    {
        return DB::transaction(function () use ($command) {
            $item = EstimateItem::findOrFail($command->attributes['item_id']);
            $estimate = $item->estimate;

            if ($estimate->status !== 'draft' && $estimate->status !== 'revised') {
                throw new \RuntimeException('Cannot update items in a finalized estimate');
            }

            $item->update([
                'quantity' => $command->attributes['quantity'],
                'unit_price' => $command->attributes['unit_price'],
                'markup_percentage' => $command->attributes['markup_percentage'],
                'notes' => $command->attributes['notes'] ?? $item->notes,
            ]);

            $this->calculator->recalculate_item_total($item);
            $this->calculator->recalculate_totals($estimate);

            return $item->fresh();
        });
    }

    private function handle_approve(EstimateCommand $command): Estimate
    {
        return DB::transaction(function () use ($command) {
            $estimate = Estimate::findOrFail($command->estimate_id);
            
            if ($estimate->status !== 'pending') {
                throw new \RuntimeException('Only pending estimates can be approved');
            }

            $estimate->status = 'approved';
            $estimate->approved_by_id = $command->user_id;
            $estimate->approved_at = now();
            $estimate->save();

            return $estimate->fresh(['items']);
        });
    }

    private function handle_revise(EstimateCommand $command): Estimate
    {
        return DB::transaction(function () use ($command) {
            $original = Estimate::findOrFail($command->estimate_id);
            
            $estimate = $original->replicate();
            $estimate->version = $original->version + 1;
            $estimate->status = 'revised';
            $estimate->created_by_id = $command->user_id;
            $estimate->updated_by_id = $command->user_id;
            $estimate->save();

            foreach ($original->items as $item) {
                $new_item = $item->replicate();
                $new_item->estimate_id = $estimate->id;
                $new_item->save();
            }

            return $estimate->fresh(['items']);
        });
    }

    private function create_estimate_item(Estimate $estimate, array $data): EstimateItem
    {
        $item = new EstimateItem();
        $item->estimate_id = $estimate->id;
        $item->category = $data['category'];
        $item->subcategory = $data['subcategory'] ?? '';
        $item->description = $data['description'];
        $item->quantity = $data['quantity'];
        $item->unit = $data['unit'];
        $item->unit_price = $data['unit_price'];
        $item->markup_percentage = $data['markup_percentage'];
        $item->notes = $data['notes'] ?? '';
        $item->save();

        $this->calculator->recalculate_item_total($item);

        return $item;
    }

    private function generate_estimate_number(int $project_id): string
    {
        $project = \App\Models\Project::findOrFail($project_id);
        $estimate_count = $project->estimates()->count() + 1;
        return sprintf('%s-EST-%03d', $project->number, $estimate_count);
    }

    private function handle_delete(EstimateCommand $command): void
    {
        $estimate = Estimate::findOrFail($command->estimate_id);
        
        if ($estimate->status === 'approved') {
            throw new \RuntimeException('Cannot delete an approved estimate');
        }

        $estimate->delete();
    }

    private function handle_delete_item(EstimateCommand $command): void
    {
        DB::transaction(function () use ($command) {
            $item = EstimateItem::findOrFail($command->attributes['item_id']);
            $estimate = $item->estimate;

            if ($estimate->status !== 'draft' && $estimate->status !== 'revised') {
                throw new \RuntimeException('Cannot delete items from a finalized estimate');
            }

            $item->delete();
            $this->calculator->recalculate_totals($estimate);
        });
    }

    private function handle_reject(EstimateCommand $command): Estimate
    {
        return DB::transaction(function () use ($command) {
            $estimate = Estimate::findOrFail($command->estimate_id);
            
            if ($estimate->status !== 'pending') {
                throw new \RuntimeException('Only pending estimates can be rejected');
            }

            $estimate->status = 'rejected';
            $estimate->notes = $command->attributes['rejection_reason'] ?? $estimate->notes;
            $estimate->updated_by_id = $command->user_id;
            $estimate->save();

            return $estimate->fresh(['items']);
        });
    }
}
