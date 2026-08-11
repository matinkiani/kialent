<?php
namespace App\Http\Controllers;

use App\Models\Order;

class OrderDeliveryFormPdfController extends Controller
{
    public function __invoke(Order $order)
    {
        $order->loadMissing(['customer', 'orderItems.product']);

        // === حالت دیباگ: نمایش مستقیم HTML ===
        return view('pdf.orders.delivery-form', [
            'order' => $order,
        ]);

        // (بعدا کدهای PDF را جایگزین کنید)
    }
}
