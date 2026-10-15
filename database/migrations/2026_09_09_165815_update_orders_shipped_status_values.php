<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Existing shipped orders are now considered only partially shipped.
        DB::table('orders')
            ->where('status', 'shipped')
            ->update(['status' => 'partially_shipped']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('orders')
            ->where('status', 'partially_shipped')
            ->update(['status' => 'shipped']);
    }
};
