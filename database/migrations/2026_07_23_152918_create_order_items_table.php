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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->index()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('box_quantity')->nullable();
            $table->decimal('backing_plate_thickness', 5, 1)->nullable();
            $table->boolean('has_shim')->default(false);
            $table->string('color')->nullable();
            $table->string('slot_type')->nullable();
            $table->string('chamfer_type')->nullable();

            $table->text('production_notes')->nullable();
            $table->text('packaging_notes')->nullable();

            $table->unsignedInteger('sort')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
