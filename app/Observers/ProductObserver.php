<?php

namespace App\Observers;

use App\Models\Product;
use RuntimeException;

class ProductObserver
{
    /**
     * محصولی که در اقلام سفارش استفاده شده قابل حذف نیست.
     *
     * This is the single source of truth so every deletion path is covered;
     * the Filament actions only hide or warn before reaching this point.
     */
    public function deleting(Product $product): void
    {
        if ($product->orderItems()->exists()) {
            throw new RuntimeException('محصول در اقلام سفارش استفاده شده است و قابل حذف نیست.');
        }
    }
}
