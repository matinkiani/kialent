<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusChart extends ChartWidget
{
    protected ?string $heading = 'وضعیت سفارش‌ها';

    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    /**
     * رنگ متمایز هر وضعیت در نمودار.
     *
     * @var array<string, string>
     */
    private const STATUS_COLORS = [
        'incomplete' => '#9ca3af',
        'initial' => '#60a5fa',
        'modified' => '#fbbf24',
        'in_queue' => '#a78bfa',
        'in_production' => '#fb923c',
        'ready_to_load' => '#2dd4bf',
        'partially_shipped' => '#38bdf8',
        'completely_shipped' => '#34d399',
    ];

    protected function getData(): array
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $data = [];
        $colors = [];

        foreach (OrderStatus::cases() as $status) {
            $count = (int) ($counts[$status->value] ?? 0);

            if ($count === 0) {
                continue;
            }

            $labels[] = $status->getLabel();
            $data[] = $count;
            $colors[] = self::STATUS_COLORS[$status->value] ?? '#94a3b8';
        }

        return [
            'datasets' => [
                [
                    'label' => 'تعداد سفارش',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
