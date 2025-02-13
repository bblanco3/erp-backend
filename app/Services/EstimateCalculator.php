<?php

namespace App\Services;

use App\Models\Estimate;
use App\Models\EstimateItem;

class EstimateCalculator
{
    public function recalculate_item_total(EstimateItem $item): void
    {
        $subtotal = $item->quantity * $item->unit_price;
        $markup = $subtotal * ($item->markup_percentage / 100);
        $item->total_price = $subtotal + $markup;
        $item->save();
    }

    public function recalculate_totals(Estimate $estimate): void
    {
        $items = $estimate->items()->get();
        
        $total_cost = 0;
        $total_markup = 0;
        $total_price = 0;

        foreach ($items as $item) {
            $subtotal = $item->quantity * $item->unit_price;
            $markup = $subtotal * ($item->markup_percentage / 100);
            
            $total_cost += $subtotal;
            $total_markup += $markup;
            $total_price += ($subtotal + $markup);
        }

        $estimate->total_cost = $total_cost;
        $estimate->total_markup = $total_markup;
        $estimate->total_price = $total_price;
        $estimate->save();
    }

    public function calculate_markup_distribution(Estimate $estimate, float $target_markup_percentage): array
    {
        $items = $estimate->items()->get();
        $total_cost = $items->sum(fn($item) => $item->quantity * $item->unit_price);
        $target_total = $total_cost * (1 + $target_markup_percentage / 100);
        
        // Calculate weighted markup for each item based on its cost contribution
        $markup_adjustments = [];
        foreach ($items as $item) {
            $item_cost = $item->quantity * $item->unit_price;
            $cost_weight = $item_cost / $total_cost;
            $target_item_total = $target_total * $cost_weight;
            $required_markup_percentage = (($target_item_total / $item_cost) - 1) * 100;
            
            $markup_adjustments[] = [
                'item_id' => $item->id,
                'current_markup' => $item->markup_percentage,
                'suggested_markup' => round($required_markup_percentage, 2),
                'difference' => round($required_markup_percentage - $item->markup_percentage, 2)
            ];
        }

        return $markup_adjustments;
    }
}
