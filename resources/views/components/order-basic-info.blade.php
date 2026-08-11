<div class="border-heavy mb-6">
    <div class="bg-gray-200 font-bold px-3 py-2 border-b-2 border-black text-sm">
        ۱. مشخصات سفارش‌دهنده و وضعیت قرارداد
    </div>
    <div class="grid grid-cols-4 gap-0 text-sm">
        <div class="border-b border-l border-black p-2 bg-gray-100 font-bold">نام مشتری:</div>
        <div class="border-b border-l border-black p-2">{{ $order->customer->name }}</div>

        <div class="border-b border-l border-black p-2 bg-gray-100 font-bold">تاریخ ثبت سفارش:</div>
        <div class="border-b border-black p-2">{{ $order->created_at ? jalali($order->created_at)->format('Y/m/d') : '---' }}</div>

        <div class="border-l border-black p-2 bg-gray-100 font-bold">برند درخواستی</div>
        <div class="border-l border-black p-2 font-bold">{{ $order->brand }}</div>

        <div class="border-l border-black p-2 bg-gray-100 font-bold">اولویت تولید:</div>
        <div class="border-l border-black p-2 font-bold">{{ $order->priority->getLabel() }}</div>
    </div>
</div>
