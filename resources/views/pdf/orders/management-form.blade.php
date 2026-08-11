<x-pdf-layout :order="$order" title="فرم مدیریت" formName="فرم جامع سفارش" docSuffix="M">

    <!-- فراخوانی اطلاعات پایه مشترک -->
    <x-order-basic-info :order="$order" />

    <!-- ملاحظات مالی (فقط مخصوص فرم مدیریت) -->
    @if($order->payment_notes)
    <div class="border-heavy mb-6">
        <div class="bg-gray-200 font-bold px-3 py-2 border-b-2 border-black text-sm">
            ۲. ملاحظات مالی و اداری
        </div>
        <div class="p-3 text-sm leading-relaxed text-justify">
            {{ $order->payment_notes }}
        </div>
    </div>
    @endif

    <!-- جدول اقلام سفارش (بدون توضیحات تولید) -->
    <div class="mb-12">
        <div class="font-bold text-md mb-2">{{ $order->payment_notes ? '۳' : '۲' }}. جزئیات فنی</div>
        <table class="w-full text-center border-collapse border-heavy">
            <thead>
                <tr class="bg-gray-200 text-sm border-b-2 border-black">
                    <th class="border-normal p-2 w-10">#</th>
                    <th class="border-normal p-2 text-right">عنوان محصول</th>
                    <th class="border-normal p-2">تعداد</th>
                    <th class="border-normal p-2">کفشک</th>
                    <th class="border-normal p-2">رنگ</th>
                    <th class="border-normal p-2">شیار</th>
                    <th class="border-normal p-2">پخ</th>
                    <th class="border-normal p-2 w-16">شیم</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($order->orderItems as $index => $item)
                <tr>
                    <td class="border-normal p-2">{{ $index + 1 }}</td>
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
                    <td class="border-normal p-2" dir="ltr">{{ $item->backing_plate_thickness ?? '---' }}</td>
                    <td class="border-normal p-2">{{ $item->color }}</td>
                    <td class="border-normal p-2">{{ $item->slot_type->getLabel() }}</td>
                    <td class="border-normal p-2">{{ $item->chamfer_type->getLabel() }}</td>
                    <td class="border-normal p-2">{{ $item->has_shim ? 'دارد' : 'ندارد' }}</td>
                </tr>
                @endforeach
            </tbody>

            <!-- اضافه شدن بخش مجموع -->
            <tfoot>
                <tr class="bg-gray-100 text-sm border-t-2 border-black font-bold">
                    <td colspan="2" class="border-normal p-2 text-left">مجموع کل:</td>
                    <td class="border-normal p-2 text-lg font-black">{{ $order->orderItems->sum('quantity') }} دست</td>
                    <td colspan="5" class="border-normal p-2"></td>
                </tr>
            </tfoot>

        </table>
    </div>

</x-pdf-layout>
