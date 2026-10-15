<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * پرمیشن سراسری برای دسترسی به صفحات «تاریخچه تغییرات».
     * در UI نقش‌های Shield به‌عنوان گروه «ActivityHistory» نمایش داده می‌شود.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::firstOrCreate([
            'name' => 'View:ActivityHistory',
            'guard_name' => 'web',
        ]);
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::where('name', 'View:ActivityHistory')->delete();
    }
};
