<?php

namespace App\Services;

use App\Models\CarbonImpactAdjustment;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CarbonImpactService
{
    public const UNIT = 'kg CO2e';

    public const CALCULATION_VERSION = 'cea-v1';

    public function recordEligiblePurchase(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedOrder->loadMissing(['payment', 'items']);

            if ($lockedOrder->payment?->status !== 'verified'
                || in_array($lockedOrder->status, ['cancelled', 'refunded', 'failed', 'rejected'], true)) {
                return;
            }

            foreach ($lockedOrder->items as $item) {
                $lockedItem = OrderItem::query()->whereKey($item->id)->lockForUpdate()->firstOrFail();

                if ($lockedItem->carbon_eligible_at !== null || ! $this->hasCompleteSnapshot($lockedItem)) {
                    continue;
                }

                $contribution = round(
                    ((float) $lockedItem->carbon_baseline_emission_factor_at_purchase
                        - (float) $lockedItem->carbon_footprint_value_at_purchase)
                        * (int) $lockedItem->carbon_quantity_at_purchase,
                    6,
                );

                $lockedItem->update([
                    'carbon_contribution' => $contribution,
                    'carbon_eligible_at' => now(),
                ]);
            }
        });
    }

    public function adjustOrderItem(
        OrderItem $orderItem,
        ?float $correctedContribution,
        string $reason,
        ?User $performer,
        string $source = 'admin',
    ): void {
        DB::transaction(function () use ($orderItem, $correctedContribution, $reason, $performer, $source): void {
            $lockedItem = OrderItem::query()->whereKey($orderItem->id)->lockForUpdate()->firstOrFail();
            $previous = $this->effectiveContributionForItem($lockedItem);

            if ($lockedItem->carbon_contribution === null || $lockedItem->carbon_eligible_at === null) {
                return;
            }

            $corrected = $correctedContribution === null ? 0.0 : round($correctedContribution, 6);

            if ($previous === $corrected) {
                return;
            }

            CarbonImpactAdjustment::query()->create([
                'order_item_id' => $lockedItem->id,
                'performed_by' => $performer?->id,
                'source' => $source,
                'previous_contribution' => $previous,
                'corrected_contribution' => $corrected,
                'adjustment' => round($corrected - $previous, 6),
                'reason' => $reason,
            ]);
        });
    }

    public function revokeOrder(Order $order, string $reason, ?User $performer): void
    {
        $order->loadMissing('items');
        foreach ($order->items as $item) {
            $this->adjustOrderItem($item, 0, $reason, $performer, 'midtrans');
        }
    }

    public function revokeFulfillment(
        OrderFulfillment $fulfillment,
        string $reason,
        ?User $performer,
        string $source = 'admin',
    ): void {
        $fulfillment->loadMissing('items');
        foreach ($fulfillment->items as $item) {
            $this->adjustOrderItem($item, 0, $reason, $performer, $source);
        }
    }

    public function aggregateEligibleContribution(): float
    {
        return $this->aggregateMetrics($this->eligibleOrderItemsQuery())['net_avoided_emissions'];
    }

    /**
     * @return array{total_avoided_emissions: float, total_additional_emissions: float, net_avoided_emissions: float}
     */
    public function marketplaceMetrics(): array
    {
        return $this->aggregateMetrics($this->eligibleOrderItemsQuery());
    }

    /**
     * @return array{has_verified_data: bool, has_actual_zero: bool}
     */
    public function impactSummary(): array
    {
        $items = $this->eligibleOrderItemsQuery();
        $expression = $this->effectiveContributionExpression();

        return [
            'has_verified_data' => (clone $items)->count() > 0,
            'has_actual_zero' => (clone $items)->whereRaw("{$expression} = 0")->exists(),
        ];
    }

    /**
     * @return array{
     *     products: Collection<int, object>,
     *     unlinked_products: Collection<int, object>,
     *     total_avoided_emissions: float,
     *     total_additional_emissions: float,
     *     net_avoided_emissions: float
     * }
     */
    public function publicImpactSummary(): array
    {
        $items = $this->eligibleOrderItemsQuery();
        $metricColumns = $this->metricColumns();

        $products = (clone $items)
            ->whereNotNull('order_items.product_id')
            ->groupBy('order_items.product_id')
            ->get(array_merge([
                'order_items.product_id',
                DB::raw('SUM(order_items.carbon_quantity_at_purchase) as eligible_sold_units'),
            ], $metricColumns))
            ->keyBy('product_id')
            ->map(fn (object $row): object => $this->withMetrics($row));

        $unlinkedProducts = (clone $items)
            ->whereNull('order_items.product_id')
            ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->leftJoin('projects', 'sellers.project_id', '=', 'projects.id')
            ->groupBy('order_items.product_name', 'sellers.store_name', 'projects.class_name')
            ->get(array_merge([
                'order_items.product_name',
                'sellers.store_name',
                'projects.class_name',
                DB::raw('SUM(order_items.carbon_quantity_at_purchase) as eligible_sold_units'),
            ], $metricColumns))
            ->map(fn (object $row): object => (object) array_merge([
                'product_name' => $row->product_name,
                'class_name' => $row->class_name ?: $row->store_name,
                'eligible_sold_units' => (int) $row->eligible_sold_units,
            ], $this->metricsForRow($row)));

        return [
            'products' => $products,
            'unlinked_products' => $unlinkedProducts,
        ] + $this->aggregateMetrics($items);
    }

    /**
     * @return array{
     *     total_avoided_emissions: float,
     *     total_additional_emissions: float,
     *     net_avoided_emissions: float,
     *     eligible_item_count: int,
     *     product_breakdown: Collection<int, object>
     * }
     */
    public function sellerDashboardSummary(Seller $seller): array
    {
        $items = $this->eligibleOrderItemsQuery()
            ->where('order_items.seller_id', $seller->id);

        $productBreakdown = (clone $items)
            ->groupBy(
                'order_items.product_id',
                'order_items.product_name',
            )
            ->orderBy('order_items.product_name')
            ->get(array_merge([
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.carbon_quantity_at_purchase) as eligible_sold_units'),
            ], $this->metricColumns()))
            ->map(fn (object $row): object => (object) array_merge([
                'product_id' => $row->product_id,
                'product_name' => $row->product_name,
                'eligible_sold_units' => (int) $row->eligible_sold_units,
            ], $this->metricsForRow($row)));

        return [
            'eligible_item_count' => (int) (clone $items)->count('order_items.id'),
            'product_breakdown' => $productBreakdown,
        ] + $this->aggregateMetrics($items);
    }

    /**
     * @return array{
     *     total_avoided_emissions: float,
     *     total_additional_emissions: float,
     *     net_avoided_emissions: float,
     *     eligible_item_count: int,
     *     class_breakdown: Collection<int, object>,
     *     order_breakdown: Collection<int, object>
     * }
     */
    public function buyerDashboardSummary(User $buyer): array
    {
        $items = $this->eligibleOrderItemsQuery()
            ->where('orders.buyer_id', $buyer->id);
        $className = 'COALESCE(order_fulfillments.class_name, projects.class_name, sellers.store_name)';

        $classBreakdown = (clone $items)
            ->leftJoin('order_fulfillments', 'order_items.order_fulfillment_id', '=', 'order_fulfillments.id')
            ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->leftJoin('projects', 'sellers.project_id', '=', 'projects.id')
            ->groupByRaw($className)
            ->orderByRaw($className)
            ->get(array_merge([
                DB::raw("{$className} as class_name"),
            ], $this->metricColumns()))
            ->map(fn (object $row): object => (object) array_merge(
                ['class_name' => $row->class_name],
                $this->metricsForRow($row),
            ));

        $orderBreakdown = (clone $items)
            ->groupBy('orders.id', 'orders.order_code', 'orders.created_at')
            ->orderByDesc('orders.created_at')
            ->get(array_merge([
                'orders.id',
                'orders.order_code',
                'orders.created_at',
            ], $this->metricColumns()))
            ->map(fn (object $row): object => (object) array_merge([
                'order_id' => $row->id,
                'order_code' => $row->order_code,
                'created_at' => $row->created_at,
            ], $this->metricsForRow($row)))
            ->keyBy('order_id');

        return [
            'eligible_item_count' => (int) (clone $items)->count('order_items.id'),
            'class_breakdown' => $classBreakdown,
            'order_breakdown' => $orderBreakdown,
        ] + $this->aggregateMetrics($items);
    }

    /**
     * @return array{
     *     total_avoided_emissions: float,
     *     total_additional_emissions: float,
     *     net_avoided_emissions: float,
     *     eligible_item_count: int,
     *     missing_snapshot_count: int,
     *     missing_factor_count: int,
     *     incomplete_items: Collection<int, object>,
     *     legacy_snapshot_count: int,
     *     unit_mismatch_count: int,
     *     adjustment_count: int,
     *     product_breakdown: Collection<int, object>,
     *     project_breakdown: Collection<int, object>,
     *     seller_breakdown: Collection<int, object>,
     *     buyer_breakdown: Collection<int, object>
     * }
     */
    public function adminDashboardSummary(): array
    {
        $eligibleItems = $this->eligibleOrderItemsQuery();
        $incompleteQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->join('users', 'orders.buyer_id', '=', 'users.id')
            ->where('payments.status', 'verified')
            ->where(function ($query): void {
                $query->whereNull('order_items.carbon_baseline_emission_factor_at_purchase')
                    ->orWhereNull('order_items.carbon_footprint_value_at_purchase')
                    ->orWhereNull('order_items.carbon_functional_unit_at_purchase')
                    ->orWhere('order_items.carbon_functional_unit_at_purchase', '')
                    ->orWhereNull('order_items.carbon_baseline_source_at_purchase')
                    ->orWhere('order_items.carbon_baseline_source_at_purchase', '')
                    ->orWhereNull('order_items.carbon_product_source_at_purchase')
                    ->orWhere('order_items.carbon_product_source_at_purchase', '')
                    ->orWhereNull('order_items.carbon_calculation_version_at_purchase')
                    ->orWhereNull('order_items.carbon_contribution')
                    ->orWhereNull('order_items.carbon_eligible_at');
            });
        $missingSnapshots = (clone $incompleteQuery)->count();
        $incompleteItems = (clone $incompleteQuery)
            ->orderByDesc('orders.created_at')
            ->limit(50)
            ->get([
                'order_items.id',
                'order_items.product_name',
                'order_items.carbon_snapshot_source',
                'orders.order_code',
                'users.name as buyer_name',
            ]);

        $missingFactors = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('payments.status', 'verified')
            ->where(function ($query): void {
                $query->whereNull('order_items.carbon_baseline_emission_factor_at_purchase')
                    ->orWhereNull('order_items.carbon_footprint_value_at_purchase');
            })
            ->count();

        $projectBreakdown = (clone $eligibleItems)
            ->leftJoin('order_fulfillments', 'order_items.order_fulfillment_id', '=', 'order_fulfillments.id')
            ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->leftJoin('projects', 'sellers.project_id', '=', 'projects.id')
            ->groupByRaw('COALESCE(order_fulfillments.project_id, projects.id), COALESCE(order_fulfillments.class_name, projects.class_name, sellers.store_name)')
            ->get(array_merge([
                DB::raw('COALESCE(order_fulfillments.project_id, projects.id) as id'),
                DB::raw('COALESCE(order_fulfillments.class_name, projects.class_name, sellers.store_name) as name'),
            ], $this->metricColumns()))
            ->map(fn (object $row): object => (object) array_merge([
                'id' => $row->id,
                'name' => $row->name,
            ], $this->metricsForRow($row)));

        $productBreakdown = (clone $eligibleItems)
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderBy('order_items.product_name')
            ->get(array_merge([
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.carbon_quantity_at_purchase) as eligible_sold_units'),
            ], $this->metricColumns()))
            ->map(fn (object $row): object => (object) array_merge([
                'product_id' => $row->product_id,
                'product_name' => $row->product_name,
                'eligible_sold_units' => (int) $row->eligible_sold_units,
            ], $this->metricsForRow($row)));

        $sellerBreakdown = (clone $eligibleItems)
            ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->leftJoin('projects', 'sellers.project_id', '=', 'projects.id')
            ->groupBy('sellers.id', 'sellers.store_name', 'projects.class_name')
            ->get(array_merge([
                'sellers.id',
                'sellers.store_name',
                'projects.class_name',
            ], $this->metricColumns()))
            ->map(fn (object $row): object => (object) array_merge([
                'id' => $row->id,
                'name' => $row->store_name,
                'class_name' => $row->class_name,
            ], $this->metricsForRow($row)));

        $buyerBreakdown = (clone $eligibleItems)
            ->join('users', 'orders.buyer_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name')
            ->get(array_merge([
                'users.id',
                'users.name',
            ], $this->metricColumns()))
            ->map(fn (object $row): object => (object) array_merge([
                'id' => $row->id,
                'name' => $row->name,
            ], $this->metricsForRow($row)));

        return [
            'eligible_item_count' => (int) (clone $eligibleItems)->count('order_items.id'),
            'missing_snapshot_count' => $missingSnapshots,
            'missing_factor_count' => $missingFactors,
            'incomplete_items' => $incompleteItems,
            'legacy_snapshot_count' => DB::table('order_items')
                ->where('carbon_snapshot_source', 'legacy_unverified')
                ->count(),
            'unit_mismatch_count' => DB::table('order_items')
                ->where(function ($query): void {
                    $query->whereNotNull('carbon_footprint_value_at_purchase')
                        ->where(function ($unitQuery): void {
                            $unitQuery->whereNull('carbon_footprint_unit_at_purchase')
                                ->orWhere('carbon_footprint_unit_at_purchase', '!=', self::UNIT);
                        });
                })
                ->count(),
            'adjustment_count' => CarbonImpactAdjustment::query()->count(),
            'product_breakdown' => $productBreakdown,
            'project_breakdown' => $projectBreakdown,
            'seller_breakdown' => $sellerBreakdown,
            'buyer_breakdown' => $buyerBreakdown,
        ] + $this->aggregateMetrics($eligibleItems);
    }

    private function eligibleOrderItemsQuery(): Builder
    {
        $adjustments = DB::table('carbon_impact_adjustments')
            ->select('order_item_id')
            ->selectRaw('SUM(adjustment) as adjustment_total')
            ->groupBy('order_item_id');

        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->leftJoinSub($adjustments, 'carbon_adjustments', function ($join): void {
                $join->on('carbon_adjustments.order_item_id', '=', 'order_items.id');
            })
            ->where('payments.status', 'verified')
            ->whereNotNull('order_items.carbon_eligible_at')
            ->whereNotNull('order_items.carbon_contribution');
    }

    /**
     * @return array{total_avoided_emissions: float, total_additional_emissions: float, net_avoided_emissions: float}
     */
    private function aggregateMetrics(Builder $items): array
    {
        $expression = $this->effectiveContributionExpression();

        return [
            'total_avoided_emissions' => (float) (clone $items)->sum(DB::raw("CASE WHEN {$expression} > 0 THEN {$expression} ELSE 0 END")),
            'total_additional_emissions' => (float) (clone $items)->sum(DB::raw("CASE WHEN {$expression} < 0 THEN -({$expression}) ELSE 0 END")),
            'net_avoided_emissions' => (float) (clone $items)->sum(DB::raw($expression)),
        ];
    }

    /**
     * @return array<int, Expression>
     */
    private function metricColumns(): array
    {
        $expression = $this->effectiveContributionExpression();

        return [
            DB::raw("SUM(CASE WHEN {$expression} > 0 THEN {$expression} ELSE 0 END) as total_avoided_emissions"),
            DB::raw("SUM(CASE WHEN {$expression} < 0 THEN -({$expression}) ELSE 0 END) as total_additional_emissions"),
            DB::raw("SUM({$expression}) as net_avoided_emissions"),
        ];
    }

    /**
     * @return array{total_avoided_emissions: float, total_additional_emissions: float, net_avoided_emissions: float}
     */
    private function metricsForRow(object $row): array
    {
        return [
            'total_avoided_emissions' => (float) $row->total_avoided_emissions,
            'total_additional_emissions' => (float) $row->total_additional_emissions,
            'net_avoided_emissions' => (float) $row->net_avoided_emissions,
        ];
    }

    private function withMetrics(object $row): object
    {
        return (object) array_merge([
            'product_id' => $row->product_id,
            'eligible_sold_units' => (int) $row->eligible_sold_units,
        ], $this->metricsForRow($row));
    }

    private function effectiveContributionForItem(OrderItem $item): float
    {
        $adjustments = (float) CarbonImpactAdjustment::query()
            ->where('order_item_id', $item->id)
            ->sum('adjustment');

        return round((float) $item->carbon_contribution + $adjustments, 6);
    }

    private function effectiveContributionExpression(): string
    {
        return 'order_items.carbon_contribution + COALESCE(carbon_adjustments.adjustment_total, 0)';
    }

    private function hasCompleteSnapshot(OrderItem $item): bool
    {
        return $item->carbon_baseline_emission_factor_at_purchase !== null
            && $item->carbon_footprint_value_at_purchase !== null
            && (float) $item->carbon_baseline_emission_factor_at_purchase >= 0
            && (float) $item->carbon_footprint_value_at_purchase >= 0
            && $item->carbon_footprint_unit_at_purchase === self::UNIT
            && filled($item->carbon_baseline_source_at_purchase)
            && filled($item->carbon_product_source_at_purchase)
            && filled($item->carbon_functional_unit_at_purchase)
            && (int) $item->carbon_quantity_at_purchase > 0
            && $item->carbon_calculation_version_at_purchase === self::CALCULATION_VERSION;
    }
}
