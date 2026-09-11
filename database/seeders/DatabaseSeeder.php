<?php

namespace Database\Seeders;

use App\Enums\BackPlateType;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::updateOrCreate(
            ['email' => 'matinkianigame@gmail.com'], // شرط جستجو (ایمیل)
            [
                'name' => 'Matin',
                'password' => Hash::make('119988990'), // رمز عبور دلخواه خود را اینجا بنویسید
            ]
        );

        $products = [
            ['technical_code' => 'WVA 21355', 'name' => 'پراید جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 20137', 'name' => 'پیکان جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 21209', 'name' => 'پژو 405 جلو', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 21463', 'name' => 'رنو L90 جلو', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 25169', 'name' => 'سمند ملی جلو', 'back_plate_type' => BackPlateType::TwoTypes],

            ['technical_code' => 'WVA 23554', 'name' => 'دنا / ELX عقب', 'back_plate_type' => BackPlateType::TwoTypes],

            ['technical_code' => 'WVA 23873', 'name' => 'ریو / تیبا جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 23597', 'name' => 'پژو 206 تیپ 2 جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 23954', 'name' => 'پژو 206 تیپ 5 جلو', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 20974', 'name' => 'پژو 206 تیپ 5 عقب', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 25916', 'name' => 'رانا جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 25413', 'name' => 'رانا عقب', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 21124', 'name' => 'مزدا وانت جلو', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 21388', 'name' => 'نیسان دیزل جلو', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 21632', 'name' => 'زانتیا 2000 جلو', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 20162', 'name' => 'زانتیا 2000 عقب', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 80139', 'name' => 'MVM X33 جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 24452', 'name' => 'تویوتا RAV4 جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 23656', 'name' => 'تویوتا RAV4 / MVM X33 عقب', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 23130', 'name' => 'برلیانس 320/330 جلو', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 24347', 'name' => 'برلیانس H220/230 جلو', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 23215', 'name' => 'مگان جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => '', 'name' => 'مگان عقب', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 21347', 'name' => 'نیسان پیکاپ / رونیز جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 24375', 'name' => 'هیوندای آزرا و سوناتا 2007 جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 80138 / 24033', 'name' => 'جک S5 جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 24034', 'name' => 'جک S5 دنده‌ای عقب', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 24320', 'name' => 'جک S5 اتومات عقب', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 23966', 'name' => 'سراتو سایپایی جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 21561', 'name' => 'ماکسیما جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 24039', 'name' => 'ماکسیما عقب', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 21373', 'name' => 'ون دلیکا جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 20863', 'name' => 'نیسان پاترول جلو', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 23290', 'name' => 'وانت کاپرا جلو', 'back_plate_type' => BackPlateType::OneType],
            ['technical_code' => 'WVA 24246', 'name' => 'شاهین / آریو جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 24670', 'name' => 'هایما S7 جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 80171', 'name' => 'تیگو 5 جلو', 'back_plate_type' => BackPlateType::TwoTypes],

            ['technical_code' => 'WVA 80183', 'name' => 'تیگو 7 نوع 2 / تیگو 8 پرومکس جلو', 'back_plate_type' => BackPlateType::TwoTypes],

            ['technical_code' => 'WVA 80176', 'name' => 'جک S3 جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 24610', 'name' => 'شاهین عقب', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 24024', 'name' => 'تویوتا پرادو / KMC T8 جلو', 'back_plate_type' => BackPlateType::ThreeTypes],
            ['technical_code' => 'WVA 24031', 'name' => 'جک J5 جلو', 'back_plate_type' => BackPlateType::TwoTypes],
            ['technical_code' => 'WVA 80208', 'name' => 'لاماری ایما جلو', 'back_plate_type' => BackPlateType::TwoTypes],

            ['technical_code' => null, 'name' => 'هایما S5', 'back_plate_type' => BackPlateType::OneType],
        ];

        foreach ($products as $data) {
            // استفاده از updateOrCreate برای جلوگیری از ساخت ردیف‌های تکراری در صورت اجرای چندباره سیدر
            $match = $data['technical_code'] !== null
            ? ['technical_code' => $data['technical_code']]
            : ['name' => $data['name']];

            Product::updateOrCreate(
                $match,
                [
                    'name' => $data['name'],
                    'back_plate_type' => $data['back_plate_type'],
                    'technical_code' => $data['technical_code'],
                ]
            );
        }

        $customers = [
            ['name' => 'درخشان پدر', 'code' => 'CD01'],
            ['name' => 'حبیب درخشان', 'code' => 'CD02'],
            ['name' => 'A محمدی', 'code' => 'CMA01'],
            ['name' => 'B محمدی', 'code' => 'CMB01'],
            ['name' => 'C محمدی', 'code' => 'CMC01'],
            ['name' => 'D محمدی', 'code' => 'CMD01'],
            ['name' => 'D محمدی', 'code' => 'CMO01'],
            ['name' => 'آقای پاداشی', 'code' => 'CP01'],
            ['name' => 'وحید ایزدی', 'code' => 'CI01'],
            ['name' => 'امید احمدی زاده', 'code' => 'CA01'],
            ['name' => 'آرش / مهدی احمدی زاده', 'code' => 'CA02'],
            ['name' => 'آقای عاشقی', 'code' => 'CA03'],
            ['name' => 'آقای بهنام فر', 'code' => 'CB01'],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['code' => $customer['code']],
                ['name' => $customer['name']]
            );
        }

    }
}
