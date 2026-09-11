<!-- فراخوانی اطلاعات پایه مشترک -->
<x-order-basic-info :order="$order" />

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
                <td class="border-normal p-2 font-bold">{{ $item->has_shim ? 'دارد' : 'ندارد' }}</td>

                <!-- ستون جدید: نمایش شیار، پخ و ضخامت -->
                <td class="border-normal p-2 text-xs text-right leading-relaxed">
                    <div><span class="font-bold text-gray-700">شیار:</span> {{ $item->slot_type->getLabel() }}</div>
                    <div><span class="font-bold text-gray-700">پخ:</span> {{ $item->chamfer_type->getLabel() }}</div>
                    <div><span class="font-bold text-gray-700">کفشک:</span> <span dir="ltr">{{ $item->backing_plate_thickness ? $item->backing_plate_thickness . ' mm' : '---' }}</span></div>
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