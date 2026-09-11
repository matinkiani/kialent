@props([
    'order',
    'formName',
    'docID' => null,
    'issueDate' => null,
])

<header class="border-b-3 mb-6 flex items-start justify-between border-black pb-4">
    {{-- از حاشیه منفی استفاده نمی‌کنیم؛ در چاپ محتوای بیرون‌زده به صفحه قبل منتقل می‌شود --}}
    <div class="w-1/3">
        <div class="flex flex-col items-center justify-center">
            <img src="{{ asset('img/logo3.png') }}" alt="Kialent Logo" class="mb-2 h-12 w-12 object-contain">
            <div>
                <h1 class="text-lg font-black">شرکت کیالنت البرز نور</h1>
            </div>
        </div>
    </div>
    <div class="w-1/3 text-center">
        <div class="mt-2 inline-block border-2 border-black px-4 py-2 text-xl font-black">
            {{ $formName }}
        </div>
    </div>
    <div class="w-1/3 space-y-2 text-left text-xs font-bold">
        <div>شماره سند: <span dir="ltr">{{ $docID ?? $order->order_number }}</span></div>
        <div>تاریخ صدور: {{ jalali($issueDate ?? now())->format('Y/m/d') }}</div>
        <div>پیوست: ندارد</div>
    </div>
</header>
