<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ubah foreign key department_id, manager_id, unit_id
     * dari cascadeOnDelete → nullOnDelete
     * agar ketika Department/Manager/Unit dihapus,
     * data Arsip TIDAK ikut terhapus (hanya di-set NULL).
     */
    public function up(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            // Drop foreign key lama
            $table->dropForeign(['department_id']);
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['unit_id']);

            // Ubah kolom menjadi nullable dulu
            $table->unsignedBigInteger('department_id')->nullable()->change();
            $table->unsignedBigInteger('manager_id')->nullable()->change();
            $table->unsignedBigInteger('unit_id')->nullable()->change();

            // Daftarkan ulang dengan nullOnDelete
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('manager_id')->references('id')->on('managers')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['unit_id']);

            $table->unsignedBigInteger('department_id')->nullable(false)->change();
            $table->unsignedBigInteger('manager_id')->nullable(false)->change();
            $table->unsignedBigInteger('unit_id')->nullable(false)->change();

            $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            $table->foreign('manager_id')->references('id')->on('managers')->cascadeOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->cascadeOnDelete();
        });
    }
};
