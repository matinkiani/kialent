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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('brand');
            $table->string('color')->nullable();
            $table->string('slot_type')->default('none');
            $table->string('chamfer_type')->default('none');
            $table->foreignId('packaging_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('priority')->default('normal');
            $table->string('status')->default('initial');
            $table->text('payment_notes')->nullable();
            $table->date('ordered_at');
            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
            $table->index('packaging_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
