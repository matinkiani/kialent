<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index()->constrained()->cascadeOnDelete();

            // شماره حواله: «شماره سفارش» + توالی هر حواله آن سفارش (مثال: 14-001-S001)
            $table->string('shipment_number')->unique();
            $table->string('driver_name')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->text('destination')->nullable();
            $table->unsignedInteger('total_master_boxes')->default(0);

            $table->date('shipped_at');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
