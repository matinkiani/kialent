<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ChamferType: string implements HasLabel
{
    case None = 'none';
    case Standard = 'standard';

    public function getLabel(): string
    {
        return match ($this) {
            self::None => 'بدون پخ',
            self::Standard => 'پخ دار',
        };
    }
}
