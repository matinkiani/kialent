<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SlotType: string implements HasLabel
{
    case None = 'none';
    case Straight = 'straight';
    case Angled = 'angled';

    public function getLabel(): string
    {
        return match ($this) {
            self::None => 'بدون شیار',
            self::Straight => 'شیار صاف',
            self::Angled => 'شیار کج',
        };
    }
}
