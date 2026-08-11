<?php
namespace App\Http\Controllers;

use App\Models\Order;

class OrderManagementFormPdfController extends Controller
{
    public function __invoke(Order $order)
    {
        $order->loadMissing(['customer', 'orderItems.product']);

        // === حالت دیباگ: نمایش مستقیم HTML در مرورگر ===
        return view('pdf.orders.management-form', [
            'order' => $order,
        ]);
        // $order->loadMissing(['customer', 'orderItems.product']);

        // return Pdf::view('pdf.orders.management-form', [
        //     'order' => $order,
        // ])
        //     ->headerView('pdf.orders.management-form-header', [
        //         'order' => $order,
        //     ])
        //     ->footerView('pdf.orders.management-form-footer', [
        //         'order' => $order,
        //     ])
        //     ->format('a4')
        //     ->landscape()
        //     ->margins(20, 12, 18, 12)
        //     ->name('order-management-form-'.$order->order_number.'.pdf')
        //     ->download();
    }
}
