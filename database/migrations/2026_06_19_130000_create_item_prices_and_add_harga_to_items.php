<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Master harga per kode barang (single source of truth)
        Schema::create('item_prices', function (Blueprint $t) {
            $t->id();
            $t->string('kode_barang', 64)->unique();
            $t->string('nama_barang', 191)->nullable();
            $t->decimal('harga', 18, 2)->default(0);
            $t->string('currency', 8)->default('IDR');
            $t->string('satuan', 32)->nullable();
            $t->text('keterangan')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
            $t->index('kode_barang');
        });

        // Snapshot harga + nilai pada item-item arsip. Diisi saat status -> Done
        // supaya history tidak ikut bergeser kalau master harga di-update.
        foreach (['arsip_adjust_items', 'arsip_mutasi_items', 'arsip_bundel_items'] as $tbl) {
            Schema::table($tbl, function (Blueprint $t) {
                $t->decimal('harga_unit', 18, 2)->nullable()->after('updated_at');
                $t->decimal('nilai_total', 18, 2)->nullable()->after('harga_unit');
                $t->timestamp('priced_at')->nullable()->after('nilai_total');
            });
        }
    }

    public function down(): void
    {
        foreach (['arsip_adjust_items', 'arsip_mutasi_items', 'arsip_bundel_items'] as $tbl) {
            Schema::table($tbl, function (Blueprint $t) {
                $t->dropColumn(['harga_unit', 'nilai_total', 'priced_at']);
            });
        }
        Schema::dropIfExists('item_prices');
    }
};
