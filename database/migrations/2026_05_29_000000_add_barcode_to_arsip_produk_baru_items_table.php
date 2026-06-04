<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arsip_produk_baru_items', function (Blueprint $table) {
            if (!Schema::hasColumn('arsip_produk_baru_items', 'barcode')) {
                $table->string('barcode', 64)->nullable()->unique()->after('product_name');
            }
            if (!Schema::hasColumn('arsip_produk_baru_items', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('keterangan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('arsip_produk_baru_items', function (Blueprint $table) {
            if (Schema::hasColumn('arsip_produk_baru_items', 'barcode')) {
                $table->dropUnique(['barcode']);
                $table->dropColumn('barcode');
            }
            if (Schema::hasColumn('arsip_produk_baru_items', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }
};
