<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderObserver
{
    /**
     * Assign a customer-scoped, sequential order number before the order is created.
     *
     * The factory's total order volume is never exposed: the sequence only reflects
     * how many orders the customer has placed, formatted as "{code}-{sequence}".
     */
    public function creating(Order $order): void
    {
        if ($order->order_number) {
            return;
        }

        $order->order_number = DB::transaction(function () use ($order): string {
            $customer = Customer::whereKey($order->customer_id)->lockForUpdate()->first();

            $sequence = $customer->next_order_sequence;

            $customer->increment('next_order_sequence');

            return sprintf('%s-%03d', $customer->code, $sequence);
        });
    }

    /**
     * When the order data changes, automatically flag it as «تغییر یافته» (Modified).
     *
     * A manual change to the status field itself always takes precedence and is respected.
     */
    public function updating(Order $order): void
    {
        if ($order->isDirty('status')) {
            return;
        }

        if ($order->getDirty()) {
            $order->status = OrderStatus::Modified;
        }
    }

    /**
     * سفارشی که حواله خروج دارد قابل حذف نیست.
     *
     * This is the single source of truth so every deletion path is covered;
     * the Filament actions only hide or warn before reaching this point.
     */
    public function deleting(Order $order): void
    {
        if ($order->shipments()->exists()) {
            throw new RuntimeException('سفارش دارای حواله خروج است و قابل حذف نیست.');
        }
    }
}
