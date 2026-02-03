<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arsip_adjust_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arsip_id')
                  ->constrained('arsips')
                  ->cascadeOnDelete();

            $table->string('product_code', 50)->nullable();
            $table->string('product_name', 150);
            $table->integer('qty_in')->default(0);
            $table->integer('qty_out')->default(0);
            $table->string('lot', 50)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_adjust_items');
    }
};
