<x-pdf-layout :order="$shipment->order"
    docID="{{ $shipment->shipment_number }}"
    :issueDate="$shipment->created_at"
    formName="رسید خروج سفارش"
    title="حواله خروج کالا">

    <!-- مشخصات خروج و لجستیک -->
    <div class="text-md mb-2 font-bold">
            ۱. مشخصات حواله خروج و راننده
        </div>
    <div class="border-heavy mb-6">

        <div class="grid grid-cols-4 gap-0 text-sm">
            <div class="border-b border-l border-black bg-gray-100 p-2 font-bold">شماره سفارش:</div>
            <div class="border-b border-l border-black p-2 font-bold">{{ $shipment->order->order_number }}</div>

            <div class="border-b border-l border-black bg-gray-100 p-2 font-bold">سفارش دهنده:</div>
            <div class="border-b  border-black p-2 font-bold">{{ $shipment->order->customer->name }}</div>

            <div class="border-b border-l border-black bg-gray-100 p-2 font-bold">برند:</div>
            <div class="border-b border-l border-black p-2">{{ $shipment->order->brand }}</div>

            <div class="border-b border-l border-black bg-gray-100 p-2 font-bold">تاریخ خروج:</div>
            <div class="border-b border-l border-black p-2">{{ jalali($shipment->shipped_at)->format('Y/m/d') }}</div>

            <div class="border-l border-black bg-gray-100 p-2 font-bold">راننده / باربری:</div>
            <div class="border-l border-black p-2">{{ $shipment->driver_name }}</div>

            <div class="border-l border-black bg-gray-100 p-2 font-bold">تعداد کل کارتن:</div>
            <div class="border-l border-black p-2 font-bold">{{ $shipment->total_master_boxes }} عدد</div>
        </div>
    </div>

    <!-- جدول اقلام خروجی -->
    <div class="mb-12">
        <div class="text-md mb-2 font-bold">۲. اقلام تحویلی به راننده</div>
        <table class="border-heavy w-full border-collapse text-center">
            <thead>
                <tr class="border-b-2 border-black bg-gray-200 text-sm">
                    <th class="border-normal w-10 p-2">#</th>
                    <th class="border-normal p-2 text-right">عنوان محصول</th>
                    <th class="border-normal w-24 p-2">تعداد سفارش</th>
                    <th class="border-normal w-24 bg-gray-300 p-2 font-black">تعداد تحویلی فعلی</th>
                    <th class="border-normal w-24 p-2">تعداد کارتن</th>
                    <th class="border-normal w-24 p-2">تعداد باقی‌مانده</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($shipment->shipmentItems as $index => $item)
                    @php
                        $orderItem = $item->orderItem;
                        // فقط حواله‌های قبلی و همین حواله شمرده می‌شوند؛ حواله‌های بعدی نه.
                        $totalShippedUpToNow = $orderItem->shipmentItems
                            ->where('shipment_id', '<=', $shipment->id)
                            ->sum('shipped_quantity');
                        $remaining = max(0, $orderItem->quantity - $totalShippedUpToNow);
                    @endphp
                    <tr>
                        <td class="border-normal p-2">{{ $index + 1 }}</td>

                        <td class="border-normal p-2 text-right font-bold">
                            {{ $orderItem->product->name }}
                            @if ($orderItem->has_shim)
                                (ش)
                            @endif
                            @if (filled($orderItem->backing_plate_thickness))
                                ({{ $orderItem->backing_plate_thickness }} م)
                            @endif
                        </td>

                        <td class="border-normal p-2 text-gray-600">
                            {{ $orderItem->quantity }} دست
                        </td>

                        <!-- تعداد تحویلی فعلی -->
                        <td class="border-normal bg-gray-100 p-2 text-md font-black">
                            {{ $item->shipped_quantity }} دست
                        </td>

                        <!-- تعداد کارتن -->
                        <td class="border-normal p-2">
                            {{ $item->box_quantity }} کارتن
                        </td>

                        <td class="border-normal p-2">
                            @if ($remaining>0)
{{ $remaining }} دست
                            @else
                                <span class="">---</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                <!-- صف مجموع -->
                <tr class="border-t-2 border-black  font-bold text-sm">
                    <td class=""></td>
                    <td class="p-2 text-start">مجموع:</td>
                    <td class="border-normal p-2 bg-gray-100">
                        {{ $shipment->shipmentItems->sum(fn($item) => $item->orderItem->quantity) }} دست
                    </td>
                    <td class="border-normal bg-gray-200 p-2">
                        {{ $shipment->shipmentItems->sum('shipped_quantity') }} دست
                    </td>
                    <td class="border-normal p-2 bg-gray-100">
                        {{ $shipment->shipmentItems->sum('box_quantity') }} کارتن
                    </td>
                    <td class="border-normal p-2 bg-gray-100">
                        {{ $shipment->shipmentItems->sum(fn($item) => max(0, $item->orderItem->quantity - $item->orderItem->shipmentItems->where('shipment_id', '<=', $shipment->id)->sum('shipped_quantity'))) }} دست
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- بخش امضاها -->
    <div class="mt-16 grid grid-cols-3 gap-4 text-center text-sm font-bold">
        <div>
            <p>نام و امضا راننده</p>
            <p class="mx-8 mt-16 border-t border-dashed border-gray-500 pt-2">نام و امضا</p>
        </div>
        <div>
            <p>نام و امضا تحویل دهنده</p>
            <p class="mx-8 mt-16 border-t border-dashed border-gray-500 pt-2">نام و امضا</p>
        </div>
        <div>
            <p> مدیریت</p>
            <p class="mx-8 mt-16 border-t border-dashed border-gray-500 pt-2">مهر مدیریت</p>
        </div>
    </div>

</x-pdf-layout>
