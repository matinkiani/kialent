<?php

namespace App\Support;

use App\Models\OrderItem;
use Ariaieboy\Jalali\Jalali;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * تجمیع تعداد (دست) اقلام سفارش‌ها برای داشبورد.
 */
final class OrderQuantities
{
    /**
     * مجموع تعداد (دست) اقلام سفارش‌ها در بازه [از، تا).
     *
     * مرز «تا» انحصاری است؛ در صورت null سقفی اعمال نمی‌شود.
     */
    public static function totalQuantityBetween(CarbonImmutable $from, ?CarbonImmutable $until = null): int
    {
        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.ordered_at', '>=', $from->toDateString());

        if ($until !== null) {
            $query->whereDate('orders.ordered_at', '<', $until->toDateString());
        }

        return (int) $query->sum('order_items.quantity');
    }

    /**
     * مجموع تعداد (دست) اقلام سفارش‌ها به تفکیک مشتری، مرتب‌شده نزولی.
     *
     * @return Collection<string, int>
     */
    public static function totalQuantityByCustomerSince(CarbonImmutable $from): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->whereDate('orders.ordered_at', '>=', $from->toDateString())
            ->groupBy('customers.name')
            ->selectRaw('customers.name as customer_name, sum(order_items.quantity) as total_quantity')
            ->orderByDesc('total_quantity')
            ->get()
            ->mapWithKeys(fn (OrderItem $item): array => [$item->customer_name => (int) $item->total_quantity]);
    }

    /**
     * مجموع تعداد (دست) اقلام سفارش‌ها روی ماه‌های شمسی، از تاریخ داده‌شده به بعد.
     *
     * اندیس ۰ = فروردین تا اندیس ۱۱ = اسفند.
     *
     * @return array<int, int>
     */
    public static function totalQuantityByJalaliMonthSince(CarbonImmutable $from): array
    {
        $totals = array_fill(0, 12, 0);

        OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.ordered_at', '>=', $from->toDateString())
            ->groupBy('orders.ordered_at')
            ->selectRaw('orders.ordered_at, sum(order_items.quantity) as total_quantity')
            ->get()
            ->each(function (OrderItem $item) use (&$totals): void {
                $month = Jalali::forge($item->ordered_at)->getMonth();

                $totals[$month - 1] += (int) $item->total_quantity;
            });

        return $totals;
    }
}
