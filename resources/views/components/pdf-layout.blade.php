<!DOCTYPE html>
<html dir="rtl"
    lang="fa">

    <head>
        <meta charset="utf-8">
        <title>{{ $title ?? 'فرم سفارش' }} - {{ $order->order_number }}</title>
        @vite(['resources/css/pdf.css'])
    </head>

    <body class="bg-gray-200 text-sm text-black print:bg-white">
        <main class="a4-preview mx-auto my-5 bg-white p-[15mm] shadow-xl print:p-0 print:my-0 print:shadow-none">

            <!-- سربرگ مشترک -->
            <x-pdf-form-header
                :order="$order"
                :formName="$formName"
                :docID="isset($docID) ? $docID : null"
                :issueDate="isset($issueDate) ? $issueDate : null"
            />

            <!-- محتوای متغیر هر فرم اینجا قرار می‌گیرد -->
            {{ $slot }}

        </main>
    </body>

</html>
