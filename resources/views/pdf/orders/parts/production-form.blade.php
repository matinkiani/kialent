<!-- فراخوانی اطلاعات پایه مشترک -->
<x-order-basic-info :order="$order" />

<!-- مشخصات فنی سفارش (سطح سفارش) -->
@include('pdf.orders.parts.order-specs')

<!-- جدول اقلام سفارش (مخصوص تولید) -->
<div class="mb-12">
    <div class="font-bold text-md mb-2">۲. جزئیات فنی و دستورکار تولید</div>
    <table class="w-full text-center border-collapse border-heavy">
        <thead>
            <tr class="bg-gray-200 text-sm border-b-2 border-black">
                <th class="border-normal p-2 w-10">#</th>
                <th class="border-normal p-2 text-right">عنوان محصول</th>
                <th class="border-normal p-2">تعداد</th>
                <th class="border-normal p-2 w-16">شیم</th>
                <th class="border-normal p-2 w-10">پخت شده</th>
                <th class="border-normal p-2 w-10">کوره رفته</th>
                <th class="border-normal p-2 w-10">رنگ شده</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            @foreach ($order->orderItems as $index => $item)

            <!-- سطر اصلی اطلاعات محصول -->
            <tr>
                <td class="border-normal p-2" @if($item->production_notes) rowspan="2" @endif>{{ $index + 1 }}</td>
                <td class="border-normal p-2 text-right">
                    <span class="font-bold">{{ $item->product->name }}</span>
                    @if ($item->product->description)
                        <div class="text-xs text-gray-700 mt-1">{{ $item->product->description }}</div>
                    @endif
                </td>
                <td class="border-normal p-2 font-black">
                    <div class="flex flex-col">
                        <span>{{ $item->quantity }} دست</span>
                        <span>{{ $item->product->backPlateBreakdown($item->quantity) }}</span>
                    </div>
                </td>
                <td class="border-normal p-2">
                    @if ($item->has_shim)
                        <span class="font-bold underline">دارد</span>
                    @else
                        ندارد
                    @endif
                </td>
                <td class="border-normal p-2"></td>
                <td class="border-normal p-2"></td>
                <td class="border-normal p-2"></td>
            </tr>

            <!-- سطر توضیحات تولید (فقط در صورت وجود نمایش داده می‌شود) -->
            @if($item->production_notes)
            <tr class="bg-gray-50">
                <td colspan="6" class="border-normal p-2 text-right text-xs">
                    <span class="font-bold text-gray-800">توضیحات تولید:</span>
                    <span class="text-gray-900 ml-2 whitespace-pre-line">{{ $item->production_notes }}</span>
                </td>
            </tr>
            @endif

            @endforeach
        </tbody>
    </table>
</div>