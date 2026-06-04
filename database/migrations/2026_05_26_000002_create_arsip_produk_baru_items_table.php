<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arsip_produk_baru_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arsip_id')
                ->constrained('arsips')
                ->cascadeOnDelete();

            $table->string('product_code', 80)->nullable();
            $table->string('product_name', 200);
            $table->string('tipe_produk', 30)->nullable();          // Stockable / Service / Consumable
            $table->string('kategori', 150)->nullable();             // "All / BB Tembaga EX" etc.
            $table->string('satuan', 30)->nullable();                // Box / Btg / Pcs / kg ...
            $table->string('status_approval', 30)->default('Waiting List'); // Done / Waiting List
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_produk_baru_items');
    }
};
