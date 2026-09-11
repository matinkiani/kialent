<!DOCTYPE html>
<html dir="rtl"
    lang="fa">

    <head>
        <meta charset="utf-8">
        <title>فرم‌های سفارش - {{ $order->order_number }}</title>
        @vite(['resources/css/pdf.css'])
    </head>

    <body class="bg-gray-200 text-sm text-black print:bg-white">

        @foreach ($forms as $form)
            {{-- هر فرم انتخابی یک کاغذ مجزا است؛ کلاس sheet باعث شکستن صفحه هنگام چاپ می‌شود --}}
            <main class="a4-preview sheet mx-auto my-5 bg-white p-[15mm] shadow-xl print:p-0 print:my-0 print:shadow-none">
                <x-pdf-form-header :order="$order" :formName="$form->getLabel()" />

                @include($form->partView())
            </main>
        @endforeach

    </body>

</html>
