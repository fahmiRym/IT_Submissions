<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arsip_personal_notes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('arsip_id');
            $t->unsignedBigInteger('user_id'); // penulis catatan
            $t->text('note');
            $t->timestamps();

            $t->index(['arsip_id', 'user_id']);
            $t->foreign('arsip_id')->references('id')->on('arsips')->cascadeOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_personal_notes');
    }
};
