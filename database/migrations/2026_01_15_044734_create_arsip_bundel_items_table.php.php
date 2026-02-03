<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arsip_bundel_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arsip_id')
                  ->constrained('arsips')
                  ->cascadeOnDelete();

            $table->string('no_doc', 100);
            $table->integer('qty');
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_bundel_items');
    }
};
