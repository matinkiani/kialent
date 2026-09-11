<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Incomplete = 'incomplete';
    case Initial = 'initial';
    case Modified = 'modified';
    case InQueue = 'in_queue';
    case InProduction = 'in_production';
    case ReadyToLoad = 'ready_to_load';
    case PartiallyShipped = 'partially_shipped';
    case CompletelyShipped = 'completely_shipped';

    public function getLabel(): string
    {
        return match ($this) {
            self::Incomplete => 'ثبت ناقص سفارش',
            self::Initial => 'ثبت اولیه',
            self::Modified => 'تغییر یافته',
            self::InQueue => 'در صف تولید',
            self::InProduction => 'در حال تولید',
            self::ReadyToLoad => 'آماده بارگیری',
            self::PartiallyShipped => 'ارسال جزئی',
            self::CompletelyShipped => 'کاملاً ارسال شده',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Incomplete => 'gray',
            self::Initial => 'info',
            self::Modified => 'warning',
            self::InQueue => 'gray',
            self::InProduction => 'warning',
            self::ReadyToLoad => 'primary',
            self::PartiallyShipped => 'warning',
            self::CompletelyShipped => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Incomplete => 'heroicon-o-pencil-square',
            self::Initial => 'heroicon-o-document-plus',
            self::Modified => 'heroicon-o-arrow-path',
            self::InQueue => 'heroicon-o-queue-list',
            self::InProduction => 'heroicon-o-cog-6-tooth',
            self::ReadyToLoad => 'heroicon-o-archive-box',
            self::PartiallyShipped => 'heroicon-o-truck',
            self::CompletelyShipped => 'heroicon-o-check-badge',
        };
    }
}
