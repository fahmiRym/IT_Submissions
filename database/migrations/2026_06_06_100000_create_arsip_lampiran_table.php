<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arsip_lampiran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('arsip_id');
            $table->string('file_path', 500);            // path relatif di disk public
            $table->string('original_name', 255);
            $table->unsignedInteger('file_size');         // bytes
            $table->char('file_hash', 64);                // sha256 (anti-tamper)
            $table->string('mime_type', 80)->default('application/pdf');
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->string('keterangan', 500)->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('arsip_id')->references('id')->on('arsips')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->index(['arsip_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_lampiran');
    }
};
