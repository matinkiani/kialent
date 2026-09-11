<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum OrderFormType: string implements HasLabel
{
    case Packaging = 'packaging';
    case Delivery = 'delivery';
    case Production = 'production';
    case Management = 'management';

    public function getLabel(): string
    {
        return match ($this) {
            self::Packaging => 'دستورکار بسته‌بندی',
            self::Delivery => 'فرم تحویل به بسته‌بندی',
            self::Production => 'دستورکار خط تولید',
            self::Management => 'فرم جامع سفارش',
        };
    }

    /**
     * نام ویوی جزئی (partial) مربوط به هر فرم.
     */
    public function partView(): string
    {
        return match ($this) {
            self::Packaging => 'pdf.orders.parts.packaging-form',
            self::Delivery => 'pdf.orders.parts.delivery-form',
            self::Production => 'pdf.orders.parts.production-form',
            self::Management => 'pdf.orders.parts.management-form',
        };
    }
}
