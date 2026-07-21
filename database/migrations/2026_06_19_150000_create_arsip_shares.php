<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arsip_shares', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('arsip_id');
            $t->unsignedBigInteger('user_id'); // penerima share
            $t->unsignedBigInteger('shared_by')->nullable(); // pemberi share
            $t->string('note', 255)->nullable();
            $t->timestamps();

            $t->unique(['arsip_id', 'user_id']);
            $t->index('user_id');
            $t->foreign('arsip_id')->references('id')->on('arsips')->cascadeOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_shares');
    }
};
