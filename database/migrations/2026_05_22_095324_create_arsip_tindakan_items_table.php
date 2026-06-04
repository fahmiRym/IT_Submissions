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
        Schema::create('arsip_tindakan_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arsip_id')->constrained('arsips')->cascadeOnDelete();

            $table->string('tindakan_in')->nullable();
            $table->text('ket_tindakan_in')->nullable();

            $table->string('tindakan_out')->nullable();
            $table->text('ket_tindakan_out')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_tindakan_items');
    }
};

