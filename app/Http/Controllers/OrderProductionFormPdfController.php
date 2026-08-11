<?php
namespace App\Http\Controllers;

use App\Models\Order;

class OrderProductionFormPdfController extends Controller
{
    public function __invoke(Order $order)
    {
        $order->loadMissing(['customer', 'orderItems.product']);

        // === حالت دیباگ: نمایش مستقیم HTML در مرورگر ===
        return view('pdf.orders.production-form', [
            'order' => $order,
        ]);

        // کدهای مربوط به تولید PDF را بعداً مانند فرم مدیریتی در اینجا از کامنت خارج کنید
    }
}
