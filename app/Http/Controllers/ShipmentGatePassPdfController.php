<?php

namespace App\Http\Controllers;

use App\Models\Shipment;

class ShipmentGatePassPdfController extends Controller
{
    public function __invoke(Shipment $shipment)
    {
        // Load relationships needed for the view
        $shipment->loadMissing(['order.customer', 'shipmentItems.orderItem.product', 'shipmentItems.orderItem.shipmentItems']);

        return view('pdf.shipments.gate-pass', [
            'shipment' => $shipment,
        ]);
    }
}
