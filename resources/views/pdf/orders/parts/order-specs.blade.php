<!-- مشخصات فنی سفارش (سطح سفارش) -->
<div class="border-heavy mb-12">
    <div class="bg-gray-200 font-bold px-3 py-2 border-b-2 border-black text-sm">
        مشخصات فنی سفارش
    </div>
    <div class="grid grid-cols-6 gap-0 text-sm">
        <div class="border-b border-l border-black p-2 bg-gray-100 font-bold">رنگ:</div>
        <div class="border-b border-l border-black p-2" dir="ltr">{{ $order->color ?? '---' }}</div>

        <div class="border-b border-l border-black p-2 bg-gray-100 font-bold">نوع شیار:</div>
        <div class="border-b border-l border-black p-2">{{ $order->slot_type->getLabel() }}</div>

        <div class="border-b border-l border-black p-2 bg-gray-100 font-bold">نوع پخ:</div>
        <div class="border-b border-l border-black p-2">{{ $order->chamfer_type->getLabel() }}</div>
    </div>
</div>
