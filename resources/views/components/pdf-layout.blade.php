<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'فرم سفارش' }} - {{ $order->order_number }}</title>
    @vite(['resources/css/pdf.css'])
</head>
<body class="bg-gray-200 text-black text-sm print:bg-white">
    <main class="a4-preview shadow-xl p-[15mm] print:p-0 print:shadow-none bg-white mx-auto my-5">

        <!-- سربرگ مشترک -->
        <header class="flex justify-between items-start border-b-3 border-black pb-4 mb-6">
            <div class="w-1/3">
                <h1 class="text-xl font-black">کیالنت البرز نور</h1>
                <h2 class="text-sm font-bold mt-1">تولیدکننده انواع لنت ترمز دیسکی</h2>
            </div>
            <div class="w-1/3 text-center">
                <div class="text-xl font-black border-2 border-black inline-block px-4 py-2 mt-2">
                    {{ $formName }}
                </div>
            </div>
            <div class="w-1/3 text-left text-xs space-y-2 font-bold">
                <div>شماره سند: <span dir="ltr">{{ $order->order_number }}-{{ $docSuffix }}</span></div>
                <div>تاریخ صدور: {{ jalali(now())->format('Y/m/d') }}</div>
                <div>پیوست: ندارد</div>
            </div>
        </header>

        <!-- محتوای متغیر هر فرم اینجا قرار می‌گیرد -->
        {{ $slot }}

    </main>
</body>
</html>
