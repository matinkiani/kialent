<?php

namespace App\Observers;

use App\Models\Customer;
use RuntimeException;

class CustomerObserver
{
    /**
     * مشتری‌ای که سفارش ثبت کرده قابل حذف نیست.
     *
     * This is the single source of truth so every deletion path is covered;
     * the Filament actions only hide or warn before reaching this point.
     */
    public function deleting(Customer $customer): void
    {
        if ($customer->orders()->exists()) {
            throw new RuntimeException('مشتری دارای سفارش است و قابل حذف نیست.');
        }
    }
}
