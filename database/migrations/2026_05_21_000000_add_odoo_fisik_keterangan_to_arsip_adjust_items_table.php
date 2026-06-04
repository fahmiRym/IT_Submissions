<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arsip_adjust_items', function (Blueprint $table) {
            // Tambahan field draft adjustment
            // Pastikan nama kolom sesuai field request: odoo, fisik, keterangan_in, keterangan_out
            if (!Schema::hasColumn('arsip_adjust_items', 'odoo')) {
                $table->string('odoo', 100)->nullable()->after('lot');
            }

            if (!Schema::hasColumn('arsip_adjust_items', 'fisik')) {
                $table->string('fisik', 100)->nullable()->after('odoo');
            }

            if (!Schema::hasColumn('arsip_adjust_items', 'keterangan_in')) {
                $table->text('keterangan_in')->nullable()->after('fisik');
            }

            if (!Schema::hasColumn('arsip_adjust_items', 'keterangan_out')) {
                $table->text('keterangan_out')->nullable()->after('keterangan_in');
            }
        });
    }

    public function down(): void
    {
        Schema::table('arsip_adjust_items', function (Blueprint $table) {
            if (Schema::hasColumn('arsip_adjust_items', 'odoo')) {
                $table->dropColumn('odoo');
            }
            if (Schema::hasColumn('arsip_adjust_items', 'fisik')) {
                $table->dropColumn('fisik');
            }
            if (Schema::hasColumn('arsip_adjust_items', 'keterangan_in')) {
                $table->dropColumn('keterangan_in');
            }
            if (Schema::hasColumn('arsip_adjust_items', 'keterangan_out')) {
                $table->dropColumn('keterangan_out');
            }
        });
    }
};

