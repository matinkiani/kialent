<?php

use App\Http\Controllers\OrderDeliveryFormPdfController;
use App\Http\Controllers\OrderFormsPdfController;
use App\Http\Controllers\OrderManagementFormPdfController;
use App\Http\Controllers\OrderPackagingFormPdfController;
use App\Http\Controllers\OrderProductionFormPdfController;
use App\Http\Controllers\ShipmentGatePassPdfController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// دانلود ترکیبی چند فرم انتخابی (هر فرم در صفحه جداگانه چاپ می‌شود)
Route::get('/orders/{order}/forms/download', OrderFormsPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('orders.forms.download');

// دانلود فرم مدیریتی
Route::get('/orders/{order}/management-form/download', OrderManagementFormPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('orders.management-form.download');

// دانلود فرم خط تولید
Route::get('/orders/{order}/production-form/download', OrderProductionFormPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('orders.production-form.download');

Route::get('/orders/{order}/delivery-form/download', OrderDeliveryFormPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('orders.delivery-form.download');

// دانلود دستورکار بسته بندی
Route::get('/orders/{order}/packaging-form/download', OrderPackagingFormPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('orders.packaging-form.download');

Route::get('/shipments/{shipment}/gate-pass/download', ShipmentGatePassPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('shipments.gate-pass.download');
