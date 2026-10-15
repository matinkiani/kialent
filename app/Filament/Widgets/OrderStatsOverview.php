<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShipmentItem;
use App\Support\JalaliPeriod;
use App\Support\OrderQuantities;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class OrderStatsOverview extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'آمار سفارش‌ها';

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $yearStart = JalaliPeriod::yearStart();
        $previousMonthStart = JalaliPeriod::previousMonthStart();
        $monthStart = JalaliPeriod::monthStart();

        $ordersThisMonth = Order::query()
            ->whereDate('ordered_at', '>=', $monthStart->toDateString())
            ->count();

        $ordersLastMonth = Order::query()
            ->whereDate('ordered_at', '>=', $previousMonthStart->toDateString())
            ->whereDate('ordered_at', '<', $monthStart->toDateString())
            ->count();

        $ordersThisYear = Order::query()
            ->whereDate('ordered_at', '>=', $yearStart->toDateString())
            ->count();

        return [
            $this->monthStat('سفارش‌های این ماه', $ordersThisMonth, $ordersLastMonth),

            $this->monthStat(
                'محصول این ماه (دست)',
                OrderQuantities::totalQuantityBetween($monthStart),
                OrderQuantities::totalQuantityBetween($previousMonthStart, $monthStart),
            ),

            Stat::make('سفارش‌های امسال', number_format($ordersThisYear))
                ->description('از ابتدای سال شمسی '.number_format(JalaliPeriod::currentYearNumber()))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('محصول امسال (دست)', number_format(OrderQuantities::totalQuantityBetween($yearStart)))
                ->description('مجموع تعداد (دست) اقلام سفارش‌های امسال')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('سفارش‌های در جریان', number_format($this->ordersWithRemaining()))
                ->description('سفارش‌هایی که هنوز بخشی از آن‌ها ارسال نشده')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make('مجموع باقیمانده (دست)', number_format($this->remainingPieces()))
                ->description('کل سفارش‌ها منهای ارسال‌شده‌ها')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
        ];
    }

    private function monthStat(string $label, int $current, int $previous): Stat
    {
        $stat = Stat::make($label, number_format($current))
            ->description('ماه شمسی '.JalaliPeriod::monthName(JalaliPeriod::currentMonthNumber()));

        if ($previous === 0) {
            return $stat->descriptionIcon('heroicon-m-minus')
                ->color('gray');
        }

        $changePercent = (int) round((($current - $previous) / $previous) * 100);

        if ($changePercent === 0) {
            return $stat->description('بدون تغییر نسبت به ماه قبل ('.number_format($previous).')')
                ->descriptionIcon('heroicon-m-minus')
                ->color('gray');
        }

        return $stat
            ->description(($changePercent > 0 ? '+' : '').number_format($changePercent).'% نسبت به ماه قبل ('.number_format($previous).')')
            ->descriptionIcon($changePercent > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($changePercent > 0 ? 'success' : 'danger');
    }

    private function ordersWithRemaining(): int
    {
        return Order::query()
            ->whereHas('orderItems', fn (Builder $query): Builder => $query
                ->whereRaw('order_items.quantity > (select coalesce(sum(si.shipped_quantity), 0) from shipment_items si where si.order_item_id = order_items.id)'))
            ->count();
    }

    private function remainingPieces(): int
    {
        $shippedPerItem = ShipmentItem::query()
            ->selectRaw('order_item_id, coalesce(sum(shipped_quantity), 0) as shipped_quantity')
            ->groupBy('order_item_id');

        $row = OrderItem::query()
            ->leftJoinSub($shippedPerItem, 'shipped', 'shipped.order_item_id', '=', 'order_items.id')
            ->selectRaw('coalesce(sum(case when order_items.quantity - coalesce(shipped.shipped_quantity, 0) > 0 then order_items.quantity - coalesce(shipped.shipped_quantity, 0) else 0 end), 0) as remaining')
            ->first();

        return (int) ($row?->remaining ?? 0);
    }
}
