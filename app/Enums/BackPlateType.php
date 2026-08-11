<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BackPlateType: string implements HasLabel
{
    case OneType = 'one_type';
    case TwoTypes = 'two_types';
    case ThreeTypes = 'three_types';

    public function getLabel(): string
    {
        return match ($this) {
            self::OneType => 'یک نوع: 4 عدد یکسان در هر ست',
            self::TwoTypes => 'دو نوع: 2 + 2 در هر ست',
            self::ThreeTypes => 'سه نوع: 2 + 1 + 1 در هر ست',
        };
    }

    public function breakdownForSets(int $sets): string
    {
        return match ($this) {
            self::OneType => (string) ($sets * 4),
            self::TwoTypes => ($sets * 2).'+'.($sets * 2),
            self::ThreeTypes => ($sets * 2).'+'.$sets.'+'.$sets,
        };
    }
}
