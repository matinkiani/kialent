<?php

namespace App\Support;

use Ariaieboy\Jalali\CalendarUtils;
use Ariaieboy\Jalali\Jalali;
use Carbon\CarbonImmutable;

/**
 * مرزهای زمانی تقویم شمسی برای آمار داشبورد.
 *
 * همه مرزها ابتدای روز (00:00) هستند و مرز «پایان» انحصاری است
 * (ابتدای روز ماه بعد) تا در شرط between قابل استفاده باشد.
 */
final class JalaliPeriod
{
    /** @var array<int, string> */
    private const MONTH_NAMES = [
        1 => 'فروردین',
        2 => 'اردیبهشت',
        3 => 'خرداد',
        4 => 'تیر',
        5 => 'مرداد',
        6 => 'شهریور',
        7 => 'مهر',
        8 => 'آبان',
        9 => 'آذر',
        10 => 'دی',
        11 => 'بهمن',
        12 => 'اسفند',
    ];

    public static function currentYearNumber(): int
    {
        return Jalali::now()->getYear();
    }

    public static function currentMonthNumber(): int
    {
        return Jalali::now()->getMonth();
    }

    /**
     * نام ماه شمسی (۱ تا ۱۲).
     */
    public static function monthName(int $monthNumber): string
    {
        return self::MONTH_NAMES[$monthNumber] ?? (string) $monthNumber;
    }

    /**
     * ابتدای سال شمسی جاری (فروردین روز ۱).
     */
    public static function yearStart(): CarbonImmutable
    {
        return self::monthStartFor(self::currentYearNumber(), 1);
    }

    /**
     * ابتدای ماه شمسی جاری.
     */
    public static function monthStart(): CarbonImmutable
    {
        $today = Jalali::now();

        return self::monthStartFor($today->getYear(), $today->getMonth());
    }

    /**
     * ابتدای یک ماه شمسی مشخص.
     */
    public static function monthStartFor(int $year, int $month): CarbonImmutable
    {
        [$gy, $gm, $gd] = CalendarUtils::toGregorian($year, $month, 1);

        return CarbonImmutable::create($gy, $gm, $gd, 0, 0, 0);
    }

    /**
     * ابتدای ماه بعد از ({$year}, {$month})؛ به‌عنوان مرز انحصاری پایان آن ماه.
     */
    public static function nextMonthStartFor(int $year, int $month): CarbonImmutable
    {
        if ($month === 12) {
            return self::monthStartFor($year + 1, 1);
        }

        return self::monthStartFor($year, $month + 1);
    }

    /**
     * ابتدای ماه شمسی بعد از «ماه جاری» (مرز انحصاری پایان ماه).
     */
    public static function nextMonthStart(): CarbonImmutable
    {
        $today = Jalali::now();

        return self::nextMonthStartFor($today->getYear(), $today->getMonth());
    }

    /**
     * ابتدای ماه شمسی قبل از «ماه جاری» (برای مقایسه روند).
     */
    public static function previousMonthStart(): CarbonImmutable
    {
        $today = Jalali::now();

        $year = $today->getYear();
        $month = $today->getMonth() - 1;

        if ($month === 0) {
            $year--;
            $month = 12;
        }

        return self::monthStartFor($year, $month);
    }

    /**
     * نام هر ۱۲ ماه سال شمسی جاری به ترتیب.
     *
     * @return array<int, string>
     */
    public static function monthNamesOfYear(): array
    {
        return array_values(self::MONTH_NAMES);
    }
}
