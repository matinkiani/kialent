<?php

namespace App\Filament\Widgets;

use App\Support\JalaliPeriod;
use App\Support\OrderQuantities;
use Filament\Widgets\ChartWidget;

class MonthlyOrdersChart extends ChartWidget
{
    protected ?string $heading = 'روند ماهانه سفارش‌ها';

    protected ?string $description = 'مجموع تعداد (دست) محصول سفارش‌ها روی ماه‌های سال شمسی جاری';

    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $totals = OrderQuantities::totalQuantityByJalaliMonthSince(JalaliPeriod::yearStart());

        return [
            'datasets' => [
                [
                    'label' => 'مجموع محصول (دست)',
                    'data' => $totals,
                    'backgroundColor' => $this->barColors($totals),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => JalaliPeriod::monthNamesOfYear(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * رنگ هر ستون بر اساس بزرگی مقدار؛ از روشن (کم) به تیره (زیاد).
     *
     * @param  array<int, int>  $totals
     * @return array<int, string>
     */
    private function barColors(array $totals): array
    {
        $max = max($totals);

        if ($max === 0) {
            return array_fill(0, count($totals), '#c7d2fe');
        }

        return array_map(fn (int $value): string => sprintf(
            'hsl(243, 75%%, %d%%)',
            82 - (int) floor(($value / $max) * 40),
        ), $totals);
    }
}
