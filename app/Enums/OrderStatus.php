<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case InProduction = 'in_production';
    case QualityCheck = 'quality_check';
    case ReadyToShip = 'ready_to_ship';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار',
            self::InProduction => 'در تولید',
            self::QualityCheck => 'کنترل کیفیت',
            self::ReadyToShip => 'آماده ارسال',
            self::Shipped => 'ارسال شده',
            self::Completed => 'تکمیل شده',
            self::Cancelled => 'لغو شده',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::InProduction => 'warning',
            self::QualityCheck => 'info',
            self::ReadyToShip => 'primary',
            self::Shipped => 'success',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::InProduction => 'heroicon-o-cog-6-tooth',
            self::QualityCheck => 'heroicon-o-magnifying-glass',
            self::ReadyToShip => 'heroicon-o-archive-box',
            self::Shipped => 'heroicon-o-truck',
            self::Completed => 'heroicon-o-check-circle',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }
}
