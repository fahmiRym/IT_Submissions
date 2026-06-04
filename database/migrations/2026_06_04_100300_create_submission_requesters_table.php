<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Pivot multi-pemohon per arsip (submission).
     * `arsip` adalah nama tabel submission existing.
     */
    public function up(): void
    {
        Schema::create('arsip_requesters', function (Blueprint $table) {
            $table->unsignedBigInteger('arsip_id');
            $table->unsignedBigInteger('user_id');
            $table->string('employee_id', 20);
            $table->string('name_snapshot', 150);
            $table->boolean('is_primary')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->primary(['arsip_id', 'user_id']);
            $table->foreign('arsip_id')->references('id')->on('arsips')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('employee_id');
            $table->index(['arsip_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_requesters');
    }
};
