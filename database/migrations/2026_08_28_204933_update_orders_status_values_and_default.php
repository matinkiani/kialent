<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Keep existing data valid by mapping the old status values onto the new ones.
        DB::table('orders')
            ->where('status', 'pending')
            ->update(['status' => 'initial']);

        DB::table('orders')
            ->where('status', 'ready_to_ship')
            ->update(['status' => 'ready_to_load']);

        DB::table('orders')
            ->where('status', 'quality_check')
            ->update(['status' => 'in_production']);

        DB::table('orders')
            ->where('status', 'completed')
            ->update(['status' => 'shipped']);

        DB::table('orders')
            ->where('status', 'cancelled')
            ->update(['status' => 'incomplete']);

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('initial')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
