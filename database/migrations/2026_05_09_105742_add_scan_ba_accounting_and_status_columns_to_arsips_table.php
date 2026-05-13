<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            // Kolom untuk menyimpan file scan BA yang diupload oleh Accounting
            $table->string('scan_ba_accounting')->nullable()->after('bukti_scan');
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropColumn('scan_ba_accounting');
        });
    }
};
