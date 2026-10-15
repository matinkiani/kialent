<?php

namespace App\Filament\Widgets;

use App\Support\JalaliPeriod;
use App\Support\OrderQuantities;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;

class OrdersByCustomerChart extends ChartWidget
{
    use HasWidgetShield;

    protected ?string $heading = 'سفارش‌های امسال به تفکیک مشتری';

    protected ?string $description = 'مجموع تعداد (دست) محصول هر مشتری در سال شمسی جاری';

    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    /**
     * @var array<int, string>
     */
    private const PALETTE = [
        '#6366f1',
        '#f43f5e',
        '#10b981',
        '#f59e0b',
        '#0ea5e9',
        '#a855f7',
        '#84cc16',
        '#ec4899',
        '#14b8a6',
        '#f97316',
        '#eab308',
        '#06b6d4',
    ];

    protected ?array $options = [
        'indexAxis' => 'y',
        'scales' => [
            'y' => [
                'reverse' => true,
            ],
        ],
    ];

    protected function getData(): array
    {
        $totals = OrderQuantities::totalQuantityByCustomerSince(JalaliPeriod::yearStart());

        return [
            'datasets' => [
                [
                    'label' => 'مجموع محصول (دست)',
                    'data' => $totals->values()->all(),
                    'backgroundColor' => self::colors($totals->count()),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $totals->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * یک رنگ از پالت برای هر مشتری؛ در صورت اتمام پالت، از ابتدا تکرار می‌شود.
     *
     * @return array<int, string>
     */
    private static function colors(int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        return array_map(
            fn (int $index): string => self::PALETTE[$index % count(self::PALETTE)],
            range(0, $count - 1),
        );
    }
}
