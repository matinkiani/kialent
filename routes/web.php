<?php

use App\Http\Controllers\OrderManagementFormPdfController;
use App\Http\Controllers\OrderProductionFormPdfController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// دانلود فرم مدیریتی
Route::get('/orders/{order}/management-form/download', OrderManagementFormPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('orders.management-form.download');

// دانلود فرم خط تولید
Route::get('/orders/{order}/production-form/download', OrderProductionFormPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('orders.production-form.download');

Route::get('/orders/{order}/delivery-form/download', \App\Http\Controllers\OrderDeliveryFormPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('orders.delivery-form.download');

// دانلود دستورکار بسته بندی
Route::get('/orders/{order}/packaging-form/download', \App\Http\Controllers\OrderPackagingFormPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('orders.packaging-form.download');
