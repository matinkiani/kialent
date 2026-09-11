<?php

namespace App\Http\Controllers;

use App\Enums\OrderFormType;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderFormsPdfController extends Controller
{
    public function __invoke(Request $request, Order $order): View
    {
        $selected = $request->query('forms', []);

        $forms = collect(is_array($selected) ? $selected : [$selected])
            ->map(fn (mixed $key): ?OrderFormType => is_string($key) ? OrderFormType::tryFrom($key) : null)
            ->filter()
            ->values();

        abort_if($forms->isEmpty(), 422, 'حداقل یک فرم باید انتخاب شده باشد.');

        $deductShipped = $request->boolean('deduct_shipped');

        $order->loadMissing(['customer', 'orderItems.product']);

        // در حالت «کم کردن ارسال شده» اقلامِ کاملاً ارسال‌شده حذف و تعداد بقیه
        // به «سفارش منهای ارسال‌شده» کاهش می‌یابد؛ همه فرم‌ها از همین داده می‌خوانند.
        $order->setRelation('orderItems', $order->orderItemsForForms($deductShipped));

        return view('pdf.orders.combined', [
            'order' => $order,
            'forms' => $forms,
        ]);
    }
}
