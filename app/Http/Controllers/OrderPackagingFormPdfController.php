<?php
namespace App\Http\Controllers;

use App\Models\Order;

class OrderPackagingFormPdfController extends Controller
{
    public function __invoke(Order $order)
    {
        $order->loadMissing(['customer', 'orderItems.product']);

        return view('pdf.orders.packaging-form', [
            'order' => $order,
        ]);

        // (بعدا کدهای PDF را جایگزین کنید)
    }
}
