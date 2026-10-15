<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('color')->nullable()->change();
            $table->string('slot_type')->nullable()->change();
            $table->string('chamfer_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('color')->change();
            $table->string('slot_type')->default('none')->change();
            $table->string('chamfer_type')->default('none')->change();
        });
    }
};
