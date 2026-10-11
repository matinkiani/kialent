<!-- فراخوانی اطلاعات پایه مشترک -->
<x-order-basic-info :order="$order" />

<!-- مشخصات فنی سفارش (سطح سفارش) -->
@include('pdf.orders.parts.order-specs')

<div class="mb-12">
    <div class="font-bold text-md mb-2">۲. جزئیات اقلام و دستورکار بسته‌بندی</div>
    <table class="w-full text-center border-collapse border-heavy">
        <thead>
            <tr class="bg-gray-200 text-sm border-b-2 border-black">
                <th class="border-normal p-2 w-10">#</th>
                <th class="border-normal p-2 text-right">عنوان محصول</th>
                <th class="border-normal p-2 w-24">تعداد کل</th>
                <th class="border-normal p-2 w-16">شیم</th>
                <th class="border-normal p-2 w-32">توضیحات</th> <!-- ستون جدید -->
                <th class="border-normal p-2">توضیحات بسته‌بندی</th>
                <th class="border-normal p-2 w-24">وضعیت انجام</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            @foreach ($order->orderItems as $index => $item)

            <tr class="border-b-2 border-black">
                <td class="border-normal p-2 font-bold text-lg">{{ $index + 1 }}</td>
                <td class="border-normal p-2 text-right">
                    <div class="font-bold">
                        {{ $item->product->name }}
                    </div>
                </td>
                <td class="border-normal px-2 font-black">
                    <span>{{ $item->quantity }} دست</span>
                </td>
                <td class="border-normal p-2">
                    @if ($item->has_shim)
                        <span class="font-bold underline">دارد</span>
                    @else
                        ندارد
                    @endif
                </td>

                <!-- ستون توضیحات: فقط موارد پرشده نمایش داده می‌شوند -->
                <td class="border-normal p-2 text-xs text-right leading-relaxed">
                    @if ($item->slot_type)
                        <div><span class="font-bold text-gray-700">شیار:</span> {{ $item->slot_type->getLabel() }}</div>
                    @endif
                    @if ($item->chamfer_type)
                        <div><span class="font-bold text-gray-700">پخ:</span> {{ $item->chamfer_type->getLabel() }}</div>
                    @endif
                    @if (filled($item->backing_plate_thickness))
                        <div><span class="font-bold text-gray-700">کفشک:</span> <span dir="ltr">{{ $item->backing_plate_thickness }} mm</span></div>
                    @endif
                    @if ($item->production_notes)
                        <div><span class="font-bold text-gray-700">تولید:</span> <span class="whitespace-pre-line">{{ $item->production_notes }}</span></div>
                    @endif
                </td>

                <td class="border-normal p-2 text-xs text-right whitespace-pre-line">{{ $item->packaging_notes ?? '---' }}</td>

                <!-- ستون خالی وضعیت انجام برای تیک زدن -->
                <td class="border-normal p-2 bg-gray-50"></td>
            </tr>

            @endforeach
            <!-- صف مجموع -->
            <tr class="border-t-2 border-black bg-gray-200 font-bold text-sm">
                <td class="border-normal p-2"></td>
                <td class="border-normal p-2 text-start">مجموع:</td>
                <td class="border-normal p-2 ">
                    {{ $order->orderItems->sum('quantity') }} دست
                </td>
                <td class=""></td>
                <td class=""></td>
                <td class=""></td>
                <td class=""></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- بخش امضاها -->
<div class="break-inside-avoid grid grid-cols-2 gap-10 mt-16 mb-4">
    <div class="text-center">
        <div class="font-bold text-sm mb-16">سرپرست بسته‌بندی</div>
        <div class="border-b-2 border-black w-3/4 mx-auto"></div>
        <div class="text-xs mt-2 text-gray-700 font-bold">نام، امضا و تاریخ</div>
    </div>

    <div class="text-center">
        <div class="font-bold text-sm mb-16">تایید نهایی و مجوز خروج</div>
        <div class="border-b-2 border-black w-3/4 mx-auto"></div>
        <div class="text-xs mt-2 text-gray-700 font-bold">نام، امضا و تاریخ</div>
    </div>
</div>