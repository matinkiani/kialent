<x-pdf-layout :order="$order" title="فرم تحویل" formName="فرم تحویل به بسته‌بندی" docSuffix="D">

    <!-- فراخوانی اطلاعات پایه مشترک -->
    <x-order-basic-info :order="$order" />

    <div class="mb-8">
        <div class="font-bold text-md mb-2">۲. گزارش تحویل مقطعی از خط تولید</div>
        <table class="w-full text-center border-collapse border-heavy">
            <thead>
                <tr class="bg-gray-200 text-sm border-b-2 border-black">
                    <th class="border-normal p-2 w-10">#</th>
                    <th class="border-normal p-2 text-right">عنوان محصول</th>
                    <th class="border-normal p-2 w-24">تعداد کل</th>
                    <th class="border-normal p-2 w-16">شیم</th>
                    <th class="border-normal p-2">توضیحات بسته‌بندی</th>
                </tr>
            </thead>

            <!-- نکته مهم: حلقه foreach را بیرون tbody قرار می‌دهیم -->
            @foreach ($order->orderItems as $index => $item)

            <!-- هر محصول در یک tbody مجزا قرار می‌گیرد که استایل جلوگیری از شکستن صفحه دارد -->
            <tbody class="text-sm break-inside-avoid" style="page-break-inside: avoid;">

                <!-- سطر اطلاعات پایه محصول -->
                <tr class="bg-gray-50 border-t-2 border-black">
                    <td class="border-normal p-2 font-bold text-lg">{{ $index + 1 }}</td>
                    <td class="border-normal p-2 text-right font-bold">
                        {{ $item->product->name }}
                    </td>
                    <td class="border-normal px-2 font-black ">
                        <span>{{ $item->quantity }} دست ({{ $item->product->backPlateBreakdown($item->quantity) }})</span>
                    </td>
                    <td class="border-normal p-2">{{ $item->has_shim ? 'دارد' : 'ندارد' }}</td>
                    <td class="border-normal p-2 text-xs text-right whitespace-pre-line">{{ $item->packaging_notes ?? '---' }}</td>
                </tr>

                <!-- مینی جدول خالی برای ثبت دفعات تحویل -->
                <tr>
                    <td colspan="5" class="border-normal p-0">
                        <table class="w-full text-center border-collapse text-xs">
                            <tr>
                                <td class="border-b border-l border-gray-400 p-1 bg-gray-100 font-bold w-20">زمان تحویل</td>
                                @for($i = 0; $i < 8; $i++)
                                    <td class="border-b border-l border-gray-400 p-1 h-7"></td>
                                @endfor
                            </tr>
                            <tr>
                                <td class="border-l border-gray-400 p-1 bg-gray-100 font-bold w-20">تعداد تحویل</td>
                                @for($i = 0; $i < 8; $i++)
                                    <td class="border-l border-gray-400 p-1 h-7"></td>
                                @endfor
                            </tr>
                        </table>
                    </td>
                </tr>

            </tbody>
            @endforeach

        </table>
    </div>

    <!-- بلوک توضیحات و امضاها که با هم در یک صفحه می‌مانند -->
    <div class="break-inside-avoid" style="page-break-inside: avoid;">

        <!-- بخش توضیحات دستی -->
        <div class="border-heavy mb-10">
            <div class="bg-gray-200 font-bold px-3 py-2 border-b-2 border-black text-sm">
                ۳. توضیحات و ملاحظات
            </div>
            <div class="px-2 pt-0 pb-3">
                <!-- خط‌چین‌های کمرنگ برای نوشتن راحت‌تر روی کاغذ -->
                <div class="border-b border-gray-300 border-dashed h-8"></div>
                <div class="border-b border-gray-300 border-dashed h-8"></div>
                <div class="border-b border-gray-300 border-dashed h-8"></div>
            </div>
        </div>

        <!-- بخش امضاها -->
        <div class="grid grid-cols-2 gap-10 mt-8 mb-4">
            <div class="text-center">
                <div class="font-bold text-sm mb-16">تحویل گیرنده</div>
                <div class="border-b-2 border-black w-3/4 mx-auto"></div>
                <div class="text-xs mt-2 text-gray-700 font-bold">نام، امضا و تاریخ</div>
            </div>

            <div class="text-center">
                <div class="font-bold text-sm mb-16"> مدیریت</div>
                <div class="border-b-2 border-black w-3/4 mx-auto"></div>
                <div class="text-xs mt-2 text-gray-700 font-bold">نام، امضا و تاریخ</div>
            </div>
        </div>

    </div>

</x-pdf-layout>
