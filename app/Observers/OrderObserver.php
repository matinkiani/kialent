<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

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
}
